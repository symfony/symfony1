<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/sfCacheDriverTests.class.php');

$plan = 121;
$t = new lime_test($plan, new lime_output_color());

if (!extension_loaded('SQLite'))
{
  $t->skip('APC needed to run these tests', $plan);
  return;
}

// ->initialize()
$t->diag('->initialize()');
$cache = sfCache::newInstance('sfSQLiteCache');
try
{
  $cache->initialize();
  $t->fail('->initialize() throws an sfInitializationException exception if you don\'t pass a "database" parameter');
}
catch (sfInitializationException $e)
{
  $t->pass('->initialize() throws an sfInitializationException exception if you don\'t pass a "database" parameter');
}

// database in memory
$cache = sfCache::newInstance('sfSQLiteCache');
$cache->initialize(array('database' => ':memory:'));

sfCacheDriverTests::launch($t, $cache);

// database on disk
$database = tempnam('/tmp/cachedir', 'tmp');
unlink($database);
$cache = sfCache::newInstance('sfSQLiteCache');
$cache->initialize(array('database' => $database));
sfCacheDriverTests::launch($t, $cache);
unlink($database);
