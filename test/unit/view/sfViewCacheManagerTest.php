<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(12, new lime_output_color());

class myController extends sfWebController
{
}

class myRequest
{
  public function getHost()
  {
    return 'localhost';
  }

  public function getScriptName()
  {
    return 'index.php';
  }
}

class myCache extends sfCache
{
  static public $cache = array();

  public function initialize()
  {
  }

  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    return isset(self::$cache[$namespace][$id]) ? self::$cache[$namespace][$id] : false;
  }

  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    return isset(self::$cache[$namespace][$id]);
  }

  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data)
  {
    self::$cache[$namespace][$id] = $data;
  }

  public function remove($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    unset(self::$cache[$namespace][$id]);
  }

  public function clean($namespace = null, $mode = 'all')
  {
    // FIXME
    print "***".$namespace."***\n";
  }

  public function lastModified($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    return time() - 60;
  }

  static public function clear()
  {
    self::$cache = array();
  }
}

$context = sfContext::getInstance(array('controller' => 'myController', 'routing' => 'sfPatternRouting', 'request' => 'myRequest'));

$r = $context->routing;
$r->connect('default', '/:module/:action/*');

// ->initialize()
$t->diag('->initialize()');
$m = new sfViewCacheManager();
$t->is($m->getContext(), null, '->initialize() takes a sfContext object as its first argument');

// ->getContext()
$t->diag('->getContext()');
$m->initialize($context, new myCache());
$t->is($m->getContext(), $context, '->getContext() returns the current context');

// ->generateNamespace()
$t->diag('->generateNamespace()');
$m = get_cache_manager($context);

// ->addCache()
$t->diag('->addCache()');
$m = get_cache_manager($context);
$m->set('test', 'module/action');
$t->is($m->has('module/action'), false, '->addCache() register a cache configuration for an action');

$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$t->is($m->get('module/action'), 'test', '->addCache() register a cache configuration for an action');

// ->set()
$t->diag('->set()');
$m = get_cache_manager($context);
$t->is($m->set('test', 'module/action'), false, '->set() returns false if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$t->is($m->set('test', 'module/action'), true, '->set() returns true if the action is cacheable');

// ->get()
$t->diag('->get()');
$m = get_cache_manager($context);
$t->is($m->get('module/action'), null, '->get() returns null if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$t->is($m->get('module/action'), 'test', '->get() returns the saved content if the action is cacheable');

// ->has()
$t->diag('->has()');
$m = get_cache_manager($context);
$t->is($m->has('module/action'), false, '->has() returns false if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$t->is($m->has('module/action'), false, '->has() returns the cache does not exist for the action');
$m->set('test', 'module/action');
$t->is($m->has('module/action'), true, '->get() returns true if the action is in cache');

// ->remove()
$t->diag('->remove()');
$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$m->remove('module/action');
$t->is($m->has('module/action'), false, '->remove() removes cache content for an action');

function get_cache_manager($context)
{
  myCache::clear();
  $m = new sfViewCacheManager();
  $m->initialize($context, new myCache());

  return $m;
}

function get_cache_config()
{
  return array(
    'withLayout'     => false,
    'lifeTime'       => 86400,
    'clientLifeTime' => 86400,
    'contextual'     => false,
    'vary'           => array(),
  );
}
