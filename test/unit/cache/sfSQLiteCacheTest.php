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

if (!extension_loaded('SQLite'))
{
  return;
}

$t = new lime_test(36, new lime_output_color());

// database in memory
sfCacheDriverTests::launch($t, new sfSQLiteCache(':memory:'));

// database on disk
$database = tempnam('/tmp/cachedir', 'tmp');
unlink($database);
sfCacheDriverTests::launch($t, new sfSQLiteCache($database));
unlink($database);
