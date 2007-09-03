<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

if (!extension_loaded('SQLite'))
{
  return false;
}

ob_start();
$t = new lime_test(5, new lime_output_color());

// initialize the storage
$database = new sfPDODatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec('CREATE TABLE session (sess_id, sess_data, sess_time)');

ini_set('session.use_cookies', 0);
$session_id = "1";

$storage = new sfPDOSessionStorage(array('db_table' => 'session', 'session_id' => $session_id, 'database' => $database));
$t->ok($storage instanceof sfStorage, 'sfPDOSessionStorage is an instance of sfStorage');
$t->ok($storage instanceof sfDatabaseSessionStorage, 'sfPDOSessionStorage is an instance of sfDatabaseSessionStorage');

// do some session operations
$_SESSION['foo'] = 'bar';
$_SESSION['bar'] = 'foo';
unset($_SESSION['foo']);
$session_data = session_encode();

// end of session
session_write_close();

// check session data in the database
$result = $connection->query(sprintf('SELECT sess_id, sess_data FROM session WHERE sess_id = "%s"', $session_id));
$data = $result->fetchAll();
$t->is(count($data), 1, 'session is stored in the database');
$t->is($data[0]['sess_data'], $session_data, 'session variables are stored in the database');

// destroy the session
$storage->sessionDestroy($session_id);
$result = $connection->query(sprintf('SELECT sess_id, sess_data FROM session WHERE sess_id = "%s"', $session_id));
$data = $result->fetchAll();
$t->is(count($data), 0, 'session is removed from the database');

// shutdown the storage
$storage->shutdown();
