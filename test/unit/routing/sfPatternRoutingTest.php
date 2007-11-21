<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(67, new lime_output_color());

class sfPatternRoutingTest extends sfPatternRouting
{
  public function getCurrentRouteName()
  {
    return $this->current_route_name;
  }
}

// public methods
$r = new sfPatternRoutingTest(new sfEventDispatcher(), array('default_module' => 'default', 'default_action' => 'index'));
foreach (array('clearRoutes', 'connect', 'generate', 'getCurrentInternalUri', 'getCurrentRouteName', 'getRoutes', 'hasRoutes', 'parse', 'setRoutes') as $method)
{
  $t->can_ok($r, $method, sprintf('"%s" is a method of sfRouting', $method));
}

// ->getRoutes()
$t->diag('->getRoutes()');
$r->clearRoutes();
$r->connect('test1', '/:module/:action');
$r->connect('test2', '/home');
$routes = $r->getRoutes();
$t->is(count($routes), 2, '->getRoutes() returns all current routes');
$t->ok(isset($routes['test1']), '->getRoutes() returns a hash indexed by route names');
$t->ok(isset($routes['test2']), '->getRoutes() returns a hash indexed by route names');

// ->setRoutes()
$t->diag('->setRoutes()');
$r->clearRoutes();
$r->connect('test1', '/:module/:action');
$r->connect('test2', '/home');
$routes = $r->getRoutes();
$r->clearRoutes();
$r->setRoutes($routes);
$t->is($r->getRoutes(), $routes, '->setRoutes() takes a routes array as its first parameter');

// ->clearRoutes()
$t->diag('->clearRoutes()');
$r->clearRoutes();
$r->connect('test1', '/:module/:action');
$r->clearRoutes();
$routes = $r->getRoutes();
$t->is(count($routes), 0, '->clearRoutes() clears all current routing rules');

// ->hasRoutes()
$t->diag('->hasRoutes()');
$r->clearRoutes();
$t->is($r->hasRoutes(), false, '->hasRoutes() returns false if there is no route');
$r->connect('test1', '/:module/:action');
$t->is($r->hasRoutes(), true, '->hasRoutes() returns true if some routes are registered');

// ->connect(), ->parse(), ->generate()
$t->diag('->connect(), ->parse(), ->generate()');

// simple routes
$r->clearRoutes();
$r->connect('test1', '/:module/:action', array('module' => 'default', 'action' => 'index1'));
$r->connect('test2', '/foo/bar', array('module' => 'default', 'action' => 'index2'));
$r->connect('test3', '/foo/:module/bar/:action', array('module' => 'default', 'action' => 'index3'));
$r->connect('test4', '/nodefault/:module/:action');

$params = array('module' => 'default', 'action' => 'index1');
$url = '/default/index1';
$t->is($r->parse($url), $params, 'parse /:module/:action route');
$t->is($r->generate('', $params), $url, 'generate /:module/:action url');

// suffix
$r->clearRoutes();
$r->setDefaultSuffix('.html');
$r->connect('foo', '/foo/:module/:action/:param.foo', array('module' => 'default', 'action' => 'index'));
$url  = '/foo/default/index/foo.foo';
$r->connect('foo1', '/foo1/:module/:action/:param1.', array('module' => 'default', 'action' => 'index1'));
$url1 = '/foo1/default/index1/foo1';
$r->connect('foo2', '/foo2/:module/:action/:param2/', array('module' => 'default', 'action' => 'index2'));
$url2 = '/foo2/default/index2/foo2/';
$r->connect('foo3', '/foo3/:module/:action/:param3', array('module' => 'default', 'action' => 'index3'));
$url3 = '/foo3/default/index3/foo3.html';

$t->is($r->generate('', array('module' => 'default', 'action' => 'index',  'param'  => 'foo'),  '/', '/', '='), $url,  '->generate() routes can override the default suffix');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1')), $url1, '->generate() routes can remove the default suffix');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2')), $url2, '->generate() routes does not have suffix when they end by /');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index3', 'param3' => 'foo3')), $url3, '->generate() routes takes a suffix defined by the "suffix" parameter');

$t->is($r->parse($url),  array('module' => 'default', 'action' => 'index',  'param'  => 'foo'),  '->parse() routes can override the default suffix');
$t->is($r->parse($url1), array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1'), '->parse() routes can remove the default suffix');
$t->is($r->parse($url2), array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2'), '->parse() routes does not have suffix when they end by /');
$t->is($r->parse($url3), array('module' => 'default', 'action' => 'index3', 'param3' => 'foo3'), '->parse() routes takes a suffix defined by the "suffix" parameter');
$r->setDefaultSuffix('.');

// duplicate names
$msg = '->connect() throws an sfConfigurationException when a route already exists with same name';
$r->clearRoutes();
$r->connect('test', '/index.php/:module/:action', array('module' => 'default', 'action' => 'index'));
try
{
  $r->connect('test', '/index.php/:module/:action', array('module' => 'default', 'action' => 'index'));

  $t->fail($msg);
}
catch (sfConfigurationException $e)
{
  $t->pass($msg);
}

// query string
$r->clearRoutes();
$r->connect('test', '/index.php/:module/:action', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/index.php/default/index?test=1&toto=2';
$t->is($r->parse($url), $params, '->parse() does not take query string into account');

// default values
$r->clearRoutes();
$r->connect('test', '/:module/:action', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index';
$t->is($r->parse($url), $params, '->parse() routes can have default values for its parameters');
$t->is($r->generate('', $params), $url, '->generate() routes can have default values for its parameters');

// params
$r->clearRoutes();
$r->connect('test', '/:module/:action/test/:id', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index', 'id' => 4);
$url = '/default/index/test/4';
$t->is($r->parse($url), $params, '->parse() routes can have parameters with no default');
$t->is($r->generate('', $params), $url, '->generate() routes can have parameters with no default');

// order
$r->clearRoutes();
$r->connect('test', '/:module/:action/test/:id/:test', array('module' => 'default', 'action' => 'index'));
$r->connect('test1', '/:module/:action/test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'foo'));
$params = array('module' => 'default', 'action' => 'index', 'id' => 'foo');
$url = '/default/index/test/foo';
$t->is($r->parse($url), $params, '->parse() takes the first matching route');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route');

// multiple params
$r->clearRoutes();
$r->connect('test', '/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'toto'));
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar');
$url = '/default/index/foo/bar';
$t->is($r->parse($url), $params, '->parse() routes have default parameters value that can be overriden');
$t->is($r->generate('', $params), $url, '->generate() routes have default parameters value that can be overriden');
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'toto');
$url = '/default/index/foo';
$t->is($r->parse($url), $params, '->parse() removes the last parameter if the parameter is default value');
//$t->is($r->generate('', $params), $url, '->generate() removes the last parameter if the parameter is default value');

// Numerics params
$r->clearRoutes();
$r->connect('test', '/:module/:action/*', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index', 15 => 'foo', 32 => 'bar', 'foo' => 'bar');
$url = '/default/index/15/foo/32/bar/foo/bar';
$t->is($r->parse($url), $params, '->parse() routes can have numeric parameters');
$t->is($r->generate('', $params), $url, '->generate() routes can have numeric parameters');


$r->clearRoutes();
$r->connect('test', '/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar'));
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar');
$url = '/default/index';
$t->is($r->parse($url), $params, '->parse() removes last parameters if they have default values');
//$t->is($r->generate('', $params), $url, '->generate() removes last parameters if they have default values');

// star parameter
$r->clearRoutes();
$r->connect('test', '/:module/:action/test/*', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index', 'page' => '4.html', 'toto' => true, 'titi' => 'toto', 'OK' => true);
$url = '/default/index/test/page/4.html/toto/1/titi/toto/OK/1';
$t->is($r->parse($url), $params, '->parse() routes can take a * as its last parameter');
$t->is($r->parse('/default/index/test/page/4.html/toto/1/titi/toto/OK/1/module/test/action/tutu'), $params, '->parse() routes can take a * as its last parameter');
$t->is($r->parse('/default/index/test/page/4.html////toto//1/titi//toto//OK/1'), $params, '->parse() routes can take a * as its last parameter');
$t->is($r->generate('', $params), $url, '->generate() routes can take a * as its last parameter');

$r->clearRoutes();
$r->connect('test',  '/:module', array('action' => 'index'));
$r->connect('test1', '/:module/:action/*', array());
$params = array('module' => 'default', 'action' => 'index', 'toto' => 'titi');
$url = '/default/index/toto/titi';
$t->is($r->parse($url), $params, '->parse() takes the first matching route but takes * into accounts');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');
$params = array('module' => 'default', 'action' => 'index');
$url = '/default';
$t->is($r->parse($url), $params, '->parse() takes the first matching route but takes * into accounts');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');

// * in the middle
$r->clearRoutes();
$r->connect('test', '/:module/:action/*/test', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index/test';
$t->is($r->parse($url), $params, '->parse() routes can take a * in the middle of a url');
$t->is($r->generate('', $params), $url, '->generate() routes can take a * in the middle of a url');
$params = array('module' => 'default', 'action' => 'index', 'foo' => true, 'bar' => 'foobar');
$url = '/default/index/foo/1/bar/foobar/test';
$t->is($r->parse($url), $params, '->parse() routes can take a * in the middle of a url');
$t->is($r->generate('', $params), $url, '->generate() routes can take a * in the middle of a url');

// requirements
$r->clearRoutes();
$r->connect('test', '/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+'));
$r->connect('test1', '/:module/:action/:id', array('module' => 'default', 'action' => 'string'));
$params = array('module' => 'default', 'action' => 'integer', 'id' => 12);
$url = '/default/integer/id/12';
$t->is($r->parse($url), $params, '->parse() routes can take requirements for parameters');
$t->is($r->generate('', $params), $url, '->generate() routes can take requirements for parameters');

$params = array('module' => 'default', 'action' => 'string', 'id' => 'NOTANINTEGER');
$url = '/default/string/NOTANINTEGER';
$t->is($r->parse($url), $params, '->parse() routes can take requirements for parameters');
$t->is($r->generate('', $params), $url, '->generate() routes can take requirements for parameters');

$r->clearRoutes();
$r->connect('test', '/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '[^/]{2}'));
$params = array('module' => 'default', 'action' => 'integer', 'id' => 'a1');
$url = '/default/integer/id/a1';
$t->is($r->parse($url), $params, '->parse() routes can take requirements for parameters');
$t->is($r->generate('', $params), $url, '->generate() routes can take requirements for parameters');

// named routes
$r->clearRoutes();
$r->connect('test', '/test/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+'));
$params = array('module' => 'default', 'action' => 'integer', 'id' => 12);
$url = '/test/12';
$named_params = array('id' => 12);
$t->is($r->generate('', $params), $url, '->generate() takes a route name as its first parameter');
$t->is($r->generate('test', $named_params), $url, '->generate() takes a route name as its first parameter');

// routing defaults parameters
$r->setDefaultParameter('foo', 'bar');
$r->clearRoutes();
$r->connect('test', '/test/:foo/:id', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index', 'id' => 12);
$url = '/test/bar/12';
$t->is($r->generate('', $params), $url, '->generate() merge parameters with defaults parameters');
$r->setDefaultParameters(array());

// empty string as a default parameter
$r->clearRoutes();
$r->connect('test', '/test/:foo', array('module' => 'default', 'action' => 'index', 'foo' => ''));
$params = array('module' => 'default', 'action' => 'index', 'foo' => '');
$url = '/test';
$t->is($r->parse($url), $params, '->parse() routes can take empty string as default parameters');

// ->appendRoute()
$t->diag('->appendRoute()');
$r->clearRoutes();
$r->connect('test',  '/:module', array('action' => 'index'));
$r->connect('test1', '/:module/:action/*', array());
$routes = $r->getRoutes();
$r->clearRoutes();
$r->appendRoute('test',  '/:module', array('action' => 'index'));
$r->appendRoute('test1', '/:module/:action/*', array());
$t->is($r->getRoutes(), $routes, '->appendRoute() is an alias for ->connect()');

// ->prependRoute()
$t->diag('->prependRoute()');
$r->clearRoutes();
$r->connect('test',  '/:module', array('action' => 'index'));
$r->connect('test1', '/:module/:action/*', array());
$route_names = array_keys($r->getRoutes());
$r->clearRoutes();
$r->prependRoute('test',  '/:module', array('action' => 'index'));
$r->prependRoute('test1', '/:module/:action/*', array());
$p_route_names = array_keys($r->getRoutes());
$t->is(implode('-', $p_route_names), implode('-', array_reverse($route_names)), '->prependRoute() adds new routes at the beginning of the existings ones');

// ->getCurrentInternalUri()
$t->diag('->getCurrentInternalUri()');
$r->clearRoutes();
$r->connect('test2', '/module/action/:id', array('module' => 'foo', 'action' => 'bar'));
$r->connect('test',  '/:module', array('action' => 'index'));
$r->connect('test1', '/:module/:action/*', array());
$r->connect('test3', '/', array());
$r->parse('/');
$t->is($r->getCurrentInternalUri(), 'default/index', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
$r->parse('/foo/bar/bar/foo/a/b');
$t->is($r->getCurrentInternalUri(), 'foo/bar?a=b&bar=foo', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
$r->parse('/module/action/2');
$t->is($r->getCurrentInternalUri(true), '@test2?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
