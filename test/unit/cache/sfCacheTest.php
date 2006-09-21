<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/util/sfToolkit.class.php');
require_once($_test_dir.'/../lib/cache/sfCache.class.php');

class myCache extends sfCache
{
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {}
  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {}
  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data) {}
  public function remove($id, $namespace = self::DEFAULT_NAMESPACE) {}
  public function clean($namespace = null, $mode = 'all') {}
  public function lastModified($id, $namespace = self::DEFAULT_NAMESPACE) {}
}

$t = new lime_test(2, new lime_output_color());

$cache = new myCache();

// ->getLifeTime() ->setLifeTime()
$t->diag('->getLifeTime() ->setLifeTime()');
$t->is($cache->getLifeTime(), 86400, '->getLifeTime() return the 86400 as the default lifetime');
$cache->setLifeTime(10);
$t->is($cache->getLifeTime(), 10, '->setLifeTime() takes a number of seconds as its first argument');
