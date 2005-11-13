<?php

require_once 'symfony/controller/sfRouting.class.php';
require_once 'symfony/exception/sfException.class.php';
require_once 'symfony/exception/sfConfigurationException.class.php';

Mock::generate('', 'sfContext');

class sfRoutingTest extends UnitTestCase
{
  private $context;
  private $routing;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
    $this->routing = sfRouting::getInstance();
    if (!defined('SF_ROUTING')) define('SF_ROUTING', true);
    if (!defined('SF_SUFFIX')) define('SF_SUFFIX', '.html');
  }

  public function test_simple()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
    );
    $url = '/HomePage/Index'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_suffix()
  {
    $r = $this->routing;
    $r->clearRoutes();

    $r->connect('foo', '/foo/:module/:action/:param.toto', array('module' => 'HomePage', 'action' => 'Index'));
    $url  = '/foo/HomePage/Index/foo.toto';
    $r->connect('foo1', '/foo1/:module/:action/:param1.r0v', array('module' => 'HomePage', 'action' => 'Index1'));
    $url1 = '/foo1/HomePage/Index1/foo1.r0v';
    $r->connect('foo2', '/foo2/:module/:action/:param2.', array('module' => 'HomePage', 'action' => 'Index2'));
    $url2 = '/foo2/HomePage/Index2/foo2';
    $r->connect('foo3', '/foo3/:module/:action/:param3/', array('module' => 'HomePage', 'action' => 'Index3'));
    $url3 = '/foo3/HomePage/Index3/foo3/';
    $r->connect('foo4', '/foo4/:module/:action/:param4', array('module' => 'HomePage', 'action' => 'Index4'));
    $url4 = '/foo4/HomePage/Index4/foo4'.SF_SUFFIX;

    $this->assertEqual($url,  $r->generate('', array('module' => 'HomePage', 'action' => 'Index',  'param'  => 'foo'),  '/', '/'));
    $this->assertEqual($url1, $r->generate('', array('module' => 'HomePage', 'action' => 'Index1', 'param1' => 'foo1'), '/', '/'));
    $this->assertEqual($url2, $r->generate('', array('module' => 'HomePage', 'action' => 'Index2', 'param2' => 'foo2'), '/', '/'));
    $this->assertEqual($url3, $r->generate('', array('module' => 'HomePage', 'action' => 'Index3', 'param3' => 'foo3'), '/', '/'));
    $this->assertEqual($url4, $r->generate('', array('module' => 'HomePage', 'action' => 'Index4', 'param4' => 'foo4'), '/', '/'));

    $this->assertEqual(array('module' => 'HomePage', 'action' => 'Index',  'param'  => 'foo'),  $r->parse($url));
    $this->assertEqual(array('module' => 'HomePage', 'action' => 'Index1', 'param1' => 'foo1'), $r->parse($url1));
    $this->assertEqual(array('module' => 'HomePage', 'action' => 'Index2', 'param2' => 'foo2'), $r->parse($url2));
    $this->assertEqual(array('module' => 'HomePage', 'action' => 'Index3', 'param3' => 'foo3'), $r->parse($url3));
    $this->assertEqual(array('module' => 'HomePage', 'action' => 'Index4', 'param4' => 'foo4'), $r->parse($url4));
  }

  public function test_duplicate_name()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/index.php/:module/:action', array('module' => 'HomePage', 'action' => 'Index'));
    try
    {
      $r->connect('test', '/index.php/:module/:action', array('module' => 'HomePage', 'action' => 'Index'));

      $this->assertTrue(0);
    }
    catch (sfConfigurationException $e)
    {
      $this->assertTrue(1);
    }
  }

  public function test_query_string()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/index.php/:module/:action', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
    );
    $url = '/index.php/HomePage/Index'.SF_SUFFIX.'?test=1&toto=2';
    $this->assertEqual($params, $r->parse($url));
  }

  public function test_default_values()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
    );
    $url = '/HomePage/Index'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_params()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/test/:id', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'id' => 4,
    );
    $url = '/HomePage/Index/test/4'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_order()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/test/:id/:test', array('module' => 'HomePage', 'action' => 'Index'));
    $r->connect('test1', '/:module/:action/test/:id', array('module' => 'HomePage', 'action' => 'Index', 'id' => 'foo'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'id' => 'foo',
    );
    $url = '/HomePage/Index/test/foo'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_multi_params()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/:test/:id', array('module' => 'HomePage', 'action' => 'Index', 'id' => 'toto'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'test' => 'foo',
      'id' => 'bar',
    );
    $url = '/HomePage/Index/foo/bar'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'test' => 'foo',
      'id' => 'toto',
    );
    $url = '/HomePage/Index/foo'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/:test/:id', array('module' => 'HomePage', 'action' => 'Index', 'test' => 'foo', 'id' => 'bar'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'test' => 'foo',
      'id' => 'bar',
    );
    $this->assertEqual($params, $r->parse('/HomePage/Index'.SF_SUFFIX));
  }

  public function test_params_star()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/test/*', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'page' => '4.html',
      'toto' => true,
      'titi' => 'toto',
      'OK' => true,
    );
    $url = '/HomePage/Index/test/page/4.html/toto/1/titi/toto/OK/1'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));
    $this->assertEqual($params, $r->parse('/HomePage/Index/test/page/4.html/toto/1/titi/toto/OK/1/module/test/action/tutu'.SF_SUFFIX));
    $this->assertEqual($params, $r->parse('/HomePage/Index/test/page/4.html////toto//1/titi//toto//OK/1'.SF_SUFFIX));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));

    $r->clearRoutes();
    $r->connect('test',  '/:module', array('action' => 'Index'));
    $r->connect('test1', '/:module/:action/*', array());

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'toto'   => 'titi',
    );
    $url = '/HomePage/Index/toto/titi'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
    );
    $url = '/HomePage'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_params_middle_star()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/*/test', array('module' => 'HomePage', 'action' => 'Index'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Index',
      'foo' => true,
      'bar' => 'foobar',
    );
    $url = '/HomePage/Index/foo/1/bar/foobar/test'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));

    $url = '/HomePage/Index/foo/1/bar/foobar/test'.SF_SUFFIX;
    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_requirements()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/:module/:action/:id', array('module' => 'HomePage', 'action' => 'Integer'), array('id' => '^\d+$'));
    $r->connect('test1', '/:module/:action/:id', array('module' => 'HomePage', 'action' => 'String'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Integer',
      'id' => 12,
    );
    $url = '/HomePage/Integer/12'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));
    $this->assertEqual($url, $r->generate('', $params, '/', '/'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'String',
      'id' => 'NOTANINTEGER',
    );
    $url = '/HomePage/String/NOTANINTEGER'.SF_SUFFIX;
    $this->assertEqual($params, $r->parse($url));
    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
  }

  public function test_named_route()
  {
    $r = $this->routing;
    $r->clearRoutes();
    $r->connect('test', '/test/:id', array('module' => 'HomePage', 'action' => 'Integer'), array('id' => '^\d+$'));

    $params = array(
      'module' => 'HomePage',
      'action' => 'Integer',
      'id' => 12,
    );
    $url = '/test/12'.SF_SUFFIX;
    $named_params = array(
      'id' => 12,
    );
    $this->assertEqual($url, $r->generate('', $params, '/', '/'));
    $this->assertEqual($url, $r->generate('test', $named_params, '/', '/'));
  }
}

?>
