<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

ob_start();
$t = new lime_test(13, new lime_output_color());

if (!extension_loaded('SQLite'))
{
  $t->skip('SQLite needed to run these tests', 5);
  exit(0);
}

// initialize the storage
$database = new sfPDODatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec('CREATE TABLE session (sess_id, sess_data, sess_time)');

ini_set('session.use_cookies', 0);
$session_id = "1";

$storage = new sfPDOSessionStorage(array('db_table' => 'session', 'session_id' => $session_id, 'database' => $database));
$t->ok($storage instanceof sfStorage, 'sfPDOSessionStorage is an instance of sfStorage');
$t->ok($storage instanceof sfDatabaseSessionStorage, 'sfPDOSessionStorage is an instance of sfDatabaseSessionStorage');

// regenerate()
$storage->regenerate(false);
$t->isnt(session_id(), $session_id, 'regenerate() regenerated the session id');
$session_id = session_id();

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

// sessionRead()
try
{
  $retrieved_data = $storage->sessionRead($session_id);
  $t->pass('sessionRead() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionRead() does not throw an exception');
}
$t->is($retrieved_data, $session_data, 'sessionRead() reads session data');

// sessionWrite()
$_SESSION['baz'] = 'woo';
$session_data = session_encode();
try
{
  $write = $storage->sessionWrite($session_id, $session_data);
  $t->pass('sessionWrite() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionWrite() does not throw an exception');
}

$t->ok($write, 'sessionWrite() returns true');
$t->is($storage->sessionRead($session_id), $session_data, 'sessionWrite() wrote session data');

// sessionGC()
try
{
  $storage->sessionGC(0);
  $t->pass('sessionGC() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionGC() does not throw an exception');
}

// destroy the session
try
{
  $storage->sessionDestroy($session_id);
  $t->pass('sessionDestroy() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionClose() does not throw an exception');
}
$result = $connection->query(sprintf('SELECT sess_id, sess_data FROM session WHERE sess_id = "%s"', $session_id));
$data = $result->fetchAll();
$t->is(count($data), 0, 'session is removed from the database');

// shutdown the storage
$storage->shutdown();
