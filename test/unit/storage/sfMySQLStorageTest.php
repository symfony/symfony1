<?php

/*
 * This file is part of the symfony package.
 * (c) 2008 Dejan Spasic <spasic.dejan@yahoo.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

if (!extension_loaded('mysql'))
{
  return false;
}

/*
$sfTestMysqlSessionStorage_DatabaseName = 'sf_unit_test';
$sfTestMysqlSessionStorage_MysqlParameters = array(
	'database' => $sfTestMysqlSessionStorage_DatabaseName,
    'username' => 'root', 'password' => '', 'method' => 'normal');
*/

ob_start();
$t = new lime_test(5, new lime_output_color());

if(isset($sfTestMysqlSessionStorage_DatabaseName, $sfTestMysqlSessionStorage_MysqlParameters))
{
  $connection = mysql_connect('localhost',
    $sfTestMysqlSessionStorage_MysqlParameters['username'],
    $sfTestMysqlSessionStorage_MysqlParameters['password'])
    or $t->fail('Can not connect to mysql server');

  mysql_query('DROP DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName, $connection);
  mysql_query('CREATE DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName, $connection)
    or $t->fail('Can not create database ' . $sfTestMysqlSessionStorage_DatabaseName);

  mysql_select_db($sfTestMysqlSessionStorage_DatabaseName, $connection);
  mysql_close($connection);

  unset($connection);

  // initialize the storage
  $database = new sfMySQLDatabase($sfTestMysqlSessionStorage_MysqlParameters);

  mysql_query("CREATE TABLE `session` (
    `sess_id` varchar(40) NOT NULL PRIMARY KEY,
    `sess_time` int(10) unsigned NOT NULL default '0',
    `sess_data` text collate utf8_unicode_ci
  ) ENGINE=MyISAM", $database->getResource())
    or $t->fail('Can not create table session');

  ini_set('session.use_cookies', 0);
  $sessionId = "1";

  $storage = new sfMySQLSessionStorage(array('db_table' => 'session',
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
  $result = mysql_query(sprintf('SELECT sess_data FROM session WHERE sess_id = "%s"', $sessionId), $database->getResource());
  list($thisSessData) = mysql_fetch_row($result);
  $t->is(mysql_num_rows($result), 1, 'session is stored in the database');
  $t->is($thisSessData, $sessionData, 'session variables are stored in the database');

  mysql_free_result($result);
  unset($thisSessData, $result);

  // destroy the session
  $storage->sessionDestroy($sessionId);
  $result = mysql_query(sprintf('SELECT COUNT(sess_id) FROM session WHERE sess_id = "%s"', $sessionId), $database->getResource());

  list($count) = mysql_fetch_row($result);
  $t->is($count, 0, 'session is removed from the database');

  mysql_free_result($result);
  unset($count, $result);

  mysql_query('DROP DATABASE ' . $sfTestMysqlSessionStorage_DatabaseName, $database->getResource());

  // shutdown the storage
  $storage->shutdown();

  // shutdown the database
  $database->shutdown();

  unset($sfTestMysqlSessionStorage_DatabaseName, $sfTestMysqlSessionStorage_MysqlParameters);
}
else
{
	$t->skip('Mysql credentials needed to run these tests', 5);
}