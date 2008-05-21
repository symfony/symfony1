<?php
/*
 * This file is part of the symfony package.
 * (c) 2008 Dejan Spasic <spasic.dejan@yahoo.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

if (!extension_loaded('mysqli'))
{
  return false;
}

$sfTestMysqlSessionStorage_DatabaseName = 'sf_unit_test';
$sfTestMysqlSessionStorage_MysqlParameters = array(
	'database' => $sfTestMysqlSessionStorage_DatabaseName,
    'username' => 'root', 'password' => '', 'method' => 'normal');

ob_start();
$t = new lime_test(5, new lime_output_color());

$connection = mysqli_connect('localhost',
  $sfTestMysqlSessionStorage_MysqlParameters['username'],
  $sfTestMysqlSessionStorage_MysqlParameters['password'])
  or $t->fail('Can not connect to mysql server');

mysqli_query($connection, 'DROP DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName);
mysqli_query($connection, 'CREATE DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName)
  or $t->fail('Can not create database ' . $sfTestMysqlSessionStorage_DatabaseName);

mysqli_select_db($connection,$sfTestMysqlSessionStorage_DatabaseName);
mysqli_close($connection);

unset($connection);

// initialize the storage
$database = new sfMySQLiDatabase($sfTestMysqlSessionStorage_MysqlParameters);

mysqli_query($database->getResource(),
"CREATE TABLE `session` (
  `sess_id` varchar(40) NOT NULL PRIMARY KEY,
  `sess_time` int(10) unsigned NOT NULL default '0',
  `sess_data` text collate utf8_unicode_ci
) ENGINE=MyISAM")
  or $t->fail('Can not create table session');

ini_set('session.use_cookies', 0);
$sessionId = "1";

$storage = new sfMySQLiSessionStorage(array('db_table' => 'session',
                                           'session_id' => $sessionId,
                                           'database' => $database));

$t->ok($storage instanceof sfStorage, 'sfMySQLSessionStorage is an instance of sfStorage');
$t->ok($storage instanceof sfDatabaseSessionStorage, 'sfMySQLSessionStorage is an instance of sfDatabaseSessionStorage');

// do some session operations
$_SESSION['foo'] = 'bar';
$_SESSION['bar'] = 'foo';
unset($_SESSION['foo']);
$sessionData = session_encode();

// end of session
session_write_close();

// check session data in the database
$result = mysqli_query($database->getResource(), sprintf('SELECT sess_data FROM session WHERE sess_id = "%s"', $sessionId));
list($thisSessData) = mysqli_fetch_row($result);
$t->is(mysqli_num_rows($result), 1, 'session is stored in the database');
$t->is($thisSessData, $sessionData, 'session variables are stored in the database');

mysqli_free_result($result);
unset($thisSessData, $result);

// destroy the session
$storage->sessionDestroy($sessionId);
$result = mysqli_query($database->getResource(), sprintf('SELECT COUNT(sess_id) FROM session WHERE sess_id = "%s"', $sessionId));

list($count) = mysqli_fetch_row($result);
$t->is($count, 0, 'session is removed from the database');

mysqli_free_result($result);
unset($count, $result);

mysqli_query($database->getResource(), 'DROP DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName);

// shutdown the storage
$storage->shutdown();

// shutdown the database
$database->shutdown();

unset($sfTestMysqlSessionStorage_DatabaseName, $sfTestMysqlSessionStorage_MysqlParameters);
