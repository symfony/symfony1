<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(123, new lime_output_color());

class sfPatternRoutingTest extends sfPatternRouting
{
  public function getCurrentRouteName()
  {
    return $this->current_route_name;
  }
}

// public methods
$r = new sfPatternRoutingTest(new sfEventDispatcher());
foreach (array('initialize', 'getCurrentInternalUri', 'getRoutes', 'setRoutes', 'hasRoutes', 'clearRoutes', 'hasRouteName', 'prependRoute', 'appendRoute', 'connect', 'generate', 'parse') as $method)
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

// ->connect()
$t->diag('->connect()');
$r->clearRoutes();
$msg = '->connect() throws an sfConfigurationException when a route already exists with same name';
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
$r->clearRoutes();
$routes = $r->connect('test', ':module/:action', array('module' => 'default', 'action' => 'index'));
$t->is($routes['test'][0], '/:module/:action', '->connect() automatically adds trailing / to route if missing');
$routes = $r->connect('test1', '', array('module' => 'default', 'action' => 'index'));
$t->is($routes['test1'][1], '/^\/*$/', '->connect() detects empty routes');
$routes = $r->connect('test2', '/', array('module' => 'default', 'action' => 'index'));
$t->is($routes['test1'][1], '/^\/*$/', '->connect() detects empty routes');

// route syntax
$t->diag('route syntax');

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

// order
$t->diag('route order');
$r->clearRoutes();
$r->connect('test', '/test/:id', array('module' => 'default1', 'action' => 'index1'), array('id' => '\d+'));
$r->connect('test1', '/test/:id', array('module' => 'default2', 'action' => 'index2'));
$params = array('module' => 'default1', 'action' => 'index1', 'id' => '12');
$url = '/test/12';
$t->is($r->parse($url), $params, '->parse()    takes the first matching route');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route');

$params = array('module' => 'default2', 'action' => 'index2', 'id' => 'foo');
$url = '/test/foo';
$t->is($r->parse($url), $params, '->parse()    takes the first matching route');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route');

$r->clearRoutes();
$r->connect('test', '/:module/:action/test/:id/:test', array('module' => 'default', 'action' => 'index'));
$r->connect('test1', '/:module/:action/test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'foo'));
$params = array('module' => 'default', 'action' => 'index', 'id' => 'foo', 'test' => null);
$url = '/default/index/test/foo';
$t->is($r->parse($url), $params, '->parse()    takes the first matching route');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route');

// suffix
$t->diag('suffix');
$r->clearRoutes();
$r->setDefaultSuffix('.html');
$r->connect('foo0', '/foo0/:module/:action/:param0', array('module' => 'default', 'action' => 'index0'));
$url0 = '/foo0/default/index0/foo0.html';
$r->connect('foo1', '/foo1/:module/:action/:param1.', array('module' => 'default', 'action' => 'index1'));
$url1 = '/foo1/default/index1/foo1';
$r->connect('foo2', '/foo2/:module/:action/:param2/', array('module' => 'default', 'action' => 'index2'));
$url2 = '/foo2/default/index2/foo2/';
$r->connect('foo3', '/foo3/:module/:action/:param3.foo', array('module' => 'default', 'action' => 'index3'));
$url3 = '/foo3/default/index3/foo3.foo';
$r->connect('foo4', '/foo4/:module/:action/:param4.:param_5', array('module' => 'default', 'action' => 'index4'));
$url4 = '/foo4/default/index4/foo.bar';

$t->is($r->generate('', array('module' => 'default', 'action' => 'index0', 'param0' => 'foo0')), $url0, '->generate() creates URL suffixed by "sf_suffix" parameter');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1')), $url1, '->generate() creates URL with no suffix when route ends with .');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2')), $url2, '->generate() creates URL with no suffix when route ends with /');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index3',  'param3'  => 'foo3'),  '/', '/', '='), $url3,  '->generate() creates URL with special suffix when route ends with .suffix');
$t->is($r->generate('', array('module' => 'default', 'action' => 'index4', 'param4' => 'foo', 'param_5' => 'bar')), $url4, '->generate() creates URL with no special suffix when route ends with .:suffix');


$t->is($r->parse($url0), array('module' => 'default', 'action' => 'index0', 'param0' => 'foo0'), '->parse() finds route from URL suffixed by "sf_suffix"');
$t->is($r->parse($url1), array('module' => 'default', 'action' => 'index1', 'param1' => 'foo1'), '->parse() finds route with no suffix when route ends with .');
$t->is($r->parse($url2), array('module' => 'default', 'action' => 'index2', 'param2' => 'foo2'), '->parse() finds route with no suffix when route ends with /');
$t->is($r->parse($url3),  array('module' => 'default', 'action' => 'index3',  'param3'  => 'foo3'),  '->parse() finds route with special suffix when route ends with .suffix');
$t->is($r->parse($url4),  array('module' => 'default', 'action' => 'index4',  'param4'  => 'foo', 'param_5' => 'bar'),  '->parse() finds route with special suffix when route ends with .:suffix');

$r->setDefaultSuffix('.');

// query string
$t->diag('query string');
$r->clearRoutes();
$r->connect('test', '/index.php/:module/:action', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/index.php/default/index?test=1&toto=2';
$t->is($r->parse($url), $params, '->parse() does not take query string into account');

// default values
$t->diag('default values');
$r->clearRoutes();
$r->connect('test', '/:module/:action', array('module' => 'default', 'action' => 'index'));
$t->is($r->generate('', array('module' => 'default')), '/default/index', 
    '->generate() creates URL for route with missing parameter if parameter is set in the default values');
$t->is($r->parse('/default'), array('module' => 'default', 'action' => 'index'), 
    '->parse()    finds route for URL   with missing parameter if parameter is set in the default values');

$r->clearRoutes();
$r->connect('test', '/:module/:action/:foo', array('module' => 'default', 'action' => 'index', 'foo' => 'bar'));
$t->is($r->generate('', array('module' => 'default')), '/default/index/bar', 
    '->generate() creates URL for route with more than one missing parameter if default values are set');
$t->is($r->parse('/default'), array('module' => 'default', 'action' => 'index', 'foo' => 'bar'), 
    '->parse()    finds route for URL   with more than one missing parameter if default values are set');

$r->clearRoutes();
$r->connect('test', '/:module/:action', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'foo', 'action' => 'bar');
$url = '/foo/bar';
$t->is($r->generate('', $params), $url, '->generate() parameters override the route default values');
$t->is($r->parse($url), $params, '->parse()    finds route with parameters distinct from the default values');

$r->clearRoutes();
$r->connect('test', '/:module/:action', array('module' => 'default'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index';
$t->is($r->generate('', $params), $url, '->generate() creates URL even if there is no default value');
$t->is($r->parse($url), $params, '->parse()    finds route even when route has no default value');

// combined examples
$r->clearRoutes();
$r->connect('test', '/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'id' => 'toto'));
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar');
$url = '/default/index/foo/bar';
$t->is($r->generate('', $params), $url, '->generate() routes have default parameters value that can be overriden');
$t->is($r->parse($url), $params, '->parse()    routes have default parameters value that can be overriden');
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'toto');
$url = '/default/index/foo';
$t->isnt($r->generate('', $params), $url, '->generate() does not remove the last parameter if the parameter is default value');
$t->is($r->parse($url), $params, '->parse()    removes the last parameter if the parameter is default value');

$r->clearRoutes();
$r->connect('test', '/:module/:action/:test/:id', array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar'));
$params = array('module' => 'default', 'action' => 'index', 'test' => 'foo', 'id' => 'bar');
$url = '/default/index';
$t->isnt($r->generate('', $params), $url, '->generate() does not remove last parameters if they have default values');
$t->is($r->parse($url), $params, '->parse()    removes last parameters if they have default values');

// routing defaults parameters
$r->setDefaultParameter('foo', 'bar');
$r->clearRoutes();
$r->connect('test', '/test/:foo/:id', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index', 'id' => 12);
$url = '/test/bar/12';
$t->is($r->generate('', $params), $url, '->generate() merges parameters with defaults from "sf_routing_defaults"');
$r->setDefaultParameters(array());

// unnamed wildcard *
$t->diag('unnamed wildcard *');
$r->clearRoutes();
$r->connect('test', '/:module/:action/test/*', array('module' => 'default', 'action' => 'index'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index/test';
$t->is($r->parse($url), $params, '->parse()    finds route for URL   with no additional parameters when route ends with unnamed wildcard *');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route with no additional parameters when route ends with unnamed wildcard *');
$params = array('module' => 'default', 'action' => 'index', 'page' => '4.html', 'toto' => true, 'titi' => 'toto', 'OK' => true);
$url = '/default/index/test/page/4.html/toto/1/titi/toto/OK/1';
$t->is($r->parse($url), $params, '->parse()    finds route for URL   with additional parameters when route ends with unnamed wildcard *');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route with additional parameters when route ends with unnamed wildcard *');
$t->is($r->parse('/default/index/test/page/4.html/toto/1/titi/toto/OK/1/module/test/action/tutu'), $params, '->parse()    does not override named wildcards with parameters passed in unnamed wildcard *');
$t->is($r->parse('/default/index/test/page/4.html////toto//1/titi//toto//OK/1'), $params, '->parse()    considers multiple separators as single in unnamed wildcard *');

// unnamed wildcard * after a token
$r->clearRoutes();
$r->connect('test',  '/:module', array('action' => 'index'));
$r->connect('test1', '/:module/:action/*', array());
$params = array('module' => 'default', 'action' => 'index', 'toto' => 'titi');
$url = '/default/index/toto/titi';
$t->is($r->parse($url), $params, '->parse()    takes the first matching route but takes * into accounts');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');
$params = array('module' => 'default', 'action' => 'index');
$url = '/default';
$t->is($r->parse($url), $params, '->parse()    takes the first matching route but takes * into accounts');
$t->is($r->generate('', $params), $url, '->generate() takes the first matching route but takes * into accounts');

// unnamed wildcard * in the middle of a rule
$t->diag('unnamed wildcard * in the middle of a rule');
$r->clearRoutes();
$r->connect('test', '/:module/:action/*/test', array('module' => 'default', 'action' => 'index'));

$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index/test';
$t->is($r->parse($url), $params, '->parse()    finds route for URL when no extra parameters are present in the URL');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when no extra parameters are added to the internal URI');

$params = array('module' => 'default', 'action' => 'index', 'foo' => true, 'bar' => 'foobar');
$url = '/default/index/foo/1/bar/foobar/test';
$t->is($r->parse($url), $params, '->parse()    finds route for URL when extra parameters are present in the URL');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when extra parameters are added to the internal URI');

// unnamed wildcard * in the middle of a rule, with a separator after distinct from /
$r->clearRoutes();
$r->connect('test', '/:module/:action/*.test', array('module' => 'default', 'action' => 'index'));

$params = array('module' => 'default', 'action' => 'index');
$url = '/default/index.test';
$t->is($r->parse($url), $params, '->parse()    finds route for URL when no extra parameters are present in the URL');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when no extra parameters are added to the internal URI');

$params = array('module' => 'default', 'action' => 'index', 'foo' => true, 'bar' => 'foobar');
$url = '/default/index/foo/1/bar/foobar.test';
$t->is($r->parse($url), $params, '->parse()    finds route for URL when extra parameters are present in the URL');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when extra parameters are added to the internal URI');

// requirements
$t->diag('requirements');
$r->clearRoutes();
$r->connect('test', '/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+'));
$r->connect('test1', '/:module/:action/:id', array('module' => 'default', 'action' => 'string'));

$params = array('module' => 'default', 'action' => 'integer', 'id' => 12);
$url = '/default/integer/id/12';
$t->is($r->parse($url), $params, '->parse()    finds route for URL   when parameters meet requirements');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when parameters meet requirements');

$params = array('module' => 'default', 'action' => 'string', 'id' => 'NOTANINTEGER');
$url = '/default/string/NOTANINTEGER';
$t->is($r->parse($url), $params, '->parse()    ignore routes when parameters don\'t meet requirements');
$t->is($r->generate('', $params), $url, '->generate() ignore routes when parameters don\'t meet requirements');

$r->clearRoutes();
$r->connect('test', '/:module/:action/id/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '[^/]{2}'));

$params = array('module' => 'default', 'action' => 'integer', 'id' => 'a1');
$url = '/default/integer/id/a1';
$t->is($r->parse($url), $params, '->parse()    finds route for URL   when parameters meet requirements');
$t->is($r->generate('', $params), $url, '->generate() creates URL for route when parameters meet requirements');

// separators
$t->diag('separators');
$r = new sfPatternRoutingTest(new sfEventDispatcher(), array('segment_separators' => array('/', ';', ':', '|', '.', '-', '+')));
$r->clearRoutes();
$r->connect('test', '/:module/:action;:foo::baz+static+:toto|:hip-:zozo.:format', array());
$r->connect('test0', '/:module/:action0', array());
$r->connect('test1', '/:module;:action1', array());
$r->connect('test2', '/:module::action2', array());
$r->connect('test3', '/:module+:action3', array());
$r->connect('test4', '/:module|:action4', array());
$r->connect('test5', '/:module.:action5', array());
$r->connect('test6', '/:module-:action6', array());
$params = array('module' => 'default', 'action' => 'index', 'action0' => 'foobar');
$url = '/default/foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by /');
$t->is($r->generate('', $params), $url, '->generate() creates routes with / separator');
$params = array('module' => 'default', 'action' => 'index', 'action1' => 'foobar');
$url = '/default;foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by ;');
$t->is($r->generate('', $params), $url, '->generate() creates routes with ; separator');
$params = array('module' => 'default', 'action' => 'index', 'action2' => 'foobar');
$url = '/default:foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by :');
$t->is($r->generate('', $params), $url, '->generate() creates routes with : separator');
$params = array('module' => 'default', 'action' => 'index', 'action3' => 'foobar');
$url = '/default+foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by +');
$t->is($r->generate('', $params), $url, '->generate() creates routes with + separator');
$params = array('module' => 'default', 'action' => 'index', 'action4' => 'foobar');
$url = '/default|foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by |');
$t->is($r->generate('', $params), $url, '->generate() creates routes with | separator');
$params = array('module' => 'default', 'action' => 'index', 'action5' => 'foobar');
$url = '/default.foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by .');
$t->is($r->generate('', $params), $url, '->generate() creates routes with . separator');
$params = array('module' => 'default', 'action' => 'index', 'action' => 'index', 'action6' => 'foobar');
$url = '/default-foobar';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by -');
$t->is($r->generate('', $params), $url, '->generate() creates routes with - separator');
$params = array('module' => 'default', 'action' => 'index', 'action' => 'foobar', 'foo' => 'bar', 'baz' => 'baz', 'toto' => 'titi', 'hip' => 'hop', 'zozo' => 'zaza', 'format' => 'xml');
$url = '/default/foobar;bar:baz+static+titi|hop-zaza.xml';
$t->is($r->parse($url), $params, '->parse()    recognizes parameters separated by mixed separators');
$t->is($r->generate('', $params), $url, '->generate() creates routes with mixed separators');
$r = new sfPatternRoutingTest(new sfEventDispatcher(), array('variable_prefixes' => array(':', '$')));

// token names
$t->diag('token names');
$r->clearRoutes();
$r->connect('test1', '/:foo_1/:bar2', array());
$params = array('module' => 'default', 'action' => 'index', 'foo_1' => 'test', 'bar2' => 'foobar');
$url = '/test/foobar';
$t->is($r->parse($url), $params, '->parse()    accepts token names composed of letters, digits and _');
$t->is($r->generate('', $params), $url, '->generate() accepts token names composed of letters, digits and _');

// token prefix
$t->diag('token prefix');
$r->clearRoutes();
$r->connect('test1', '/1/:module/:action', array());
$r->connect('test2', '/2/$module/$action/$id', array());
$r->connect('test3', '/3/$module/:action/$first_name/:last_name', array());
$params1 = array('module' => 'foo', 'action' => 'bar');
$url1 = '/1/foo/bar';
$t->is($r->parse($url1), $params1, '->parse()    accepts token names starting with :');
$t->is($r->generate('', $params1), $url1, '->generate() accepts token names starting with :');
$params2 = array('module' => 'foo', 'action' => 'bar', 'id' => 12);
$url2 = '/2/foo/bar/12';
$t->is($r->parse($url2), $params2, '->parse()    accepts token names starting with $');
$t->is($r->generate('', $params2), $url2, '->generate() accepts token names starting with $');
$params3 = array('module' => 'foo', 'action' => 'bar', 'first_name' => 'John', 'last_name' => 'Doe');
$url3 = '/3/foo/bar/John/Doe';
$t->is($r->parse($url3), $params3, '->parse()    accepts token names starting with mixed : and $');
$t->is($r->generate('', $params3), $url3, '->generate() accepts token names starting with mixed : and $');

// named routes
$t->diag('named routes');
$r->clearRoutes();
$r->connect('test', '/test/:id', array('module' => 'default', 'action' => 'integer'), array('id' => '\d+'));
$params = array('module' => 'default', 'action' => 'integer', 'id' => 12);
$url = '/test/12';
$named_params = array('id' => 12);
$t->is($r->generate('', $params), $url, '->generate() can take an empty route name as its first parameter');
$t->is($r->generate('test', $params), $url, '->generate() can take a route name as its first parameter');
$t->is($r->generate('test', $named_params), $url, '->generate() with named routes needs only parameters not defined in route default');

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

// these tests are against r7363
$t->is($r->getCurrentInternalUri(false), 'foo/bar?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
$t->is($r->getCurrentInternalUri(true), '@test2?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');
$t->is($r->getCurrentInternalUri(false), 'foo/bar?id=2', '->getCurrentInternalUri() returns the internal URI for last parsed URL');

// defaults
$t->diag('defaults');
$r->clearRoutes();
$r->connect('test', '/test', array('bar' => 'foo'));
$params = array('module' => 'default', 'action' => 'index');
$url = '/test';
$t->is($r->generate('', $params), $url, '->generate() routes takes default values into account when matching a route');
$params = array('module' => 'default', 'action' => 'index', 'bar' => 'foo');
$t->is($r->generate('', $params), $url, '->generate() routes takes default values into account when matching a route');
$params = array('module' => 'default', 'action' => 'index', 'bar' => 'bar');
try
{
  $r->generate('', $params);
  $t->fail('->generate() throws a sfConfigurationException if no route matches the params');
}
catch (sfConfigurationException $e)
{
  $t->pass('->generate() throws a sfConfigurationException if no route matches the params');
}

// mandatory parameters
$t->diag('mandatory parameters');
$r->clearRoutes();
$r->connect('test', '/test/:foo/:bar');
$params = array('foo' => 'bar');
try
{
  $r->generate('test', $params);
  $t->fail('->generate() throws a InvalidArgumentException if some mandatory parameters are not provided');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->generate() throws a InvalidArgumentException if some mandatory parameters are not provided');
}

// parameters can be empty
$t->diag('parameters can be empty');
$r->clearRoutes();
$r->connect('test', '/test/:foo/:bar');
$t->is($r->generate('test', array('foo' => 'bar', 'bar' => '')), '/test/bar/', '->generate() can take empty parameters');

// default module/action overriding
$t->diag('module/action overriding');
$r->clearRoutes();
$r->connect('test', '/', array('module' => 'default1', 'action' => 'default1'));
$params = array('module' => 'default1', 'action' => 'default1');
$t->is($r->parse('/'), $params, '->parse()    overrides the default module/action if provided in the defaults');
$t->is($r->generate('', $params), '/', '->generate() overrides the default module/action if provided in the defaults');
