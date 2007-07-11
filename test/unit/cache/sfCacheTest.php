<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class myCache extends sfCache
{
  public function get($key, $default = null) {}
  public function has($key) {}
  public function set($key, $data, $lifetime = null) {}
  public function remove($key) {}
  public function clean($mode = sfCache::ALL) {}
  public function getTimeout($key) {}
  public function getLastModified($key) {}
  public function removePattern($pattern, $delimiter = ':') {}
}

class fakeCache
{
}

$t = new lime_test(4, new lime_output_color());

// ::newInstance()
$t->diag('::newInstance()');
$t->isa_ok(sfCache::newInstance('myCache'), 'myCache', '::newInstance() takes a cache class as its first parameter');
$t->isa_ok(sfCache::newInstance('myCache'), 'myCache', '::newInstance() returns an instance of myCache');

try
{
  sfCache::newInstance('fakeCache');
  $t->fail('::newInstance() throws a sfFactoryException if the class does not extends sfCache');
}
catch (sfFactoryException $e)
{
  $t->pass('::newInstance() throws a sfFactoryException if the class does not extends sfCache');
}

// ->initialize()
$t->diag('->initialize()');
$cache = sfCache::newInstance('myCache');
$cache->initialize(array('foo' => 'bar'));
$t->is($cache->getParameterHolder()->get('foo'), 'bar', '->initialize() takes an array of parameters as its first argument');
