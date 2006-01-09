<?php

require_once 'symfony/config/sfConfig.class.php';
require_once 'symfony/request/sfRequest.class.php';
require_once 'symfony/request/sfWebRequest.class.php';

Mock::generate('sfContext');

class sfRequestTest extends UnitTestCase
{
  private
    $context = null,
    $request = null;

  public function SetUp()
  {
    sfRouting::getInstance()->clearRoutes();

    // can't initialize directly the sfRequest class (abstract)
    // using sfWebRequest class to test sfRequest

    sfConfig::set('sf_stats', false);
    sfConfig::set('sf_path_info_array', 'SERVER');
    sfConfig::set('sf_path_info_key', true);
    sfConfig::set('sf_logging_active', false);
    sfConfig::set('sf_i18n', 0);
    $this->populateVariables('/', true);

    $this->context = new MockSfContext($this);
    $this->request = sfRequest::newInstance('sfWebRequest');
    $this->request->initialize($this->context);
  }

  public function test_single_error()
  {
    $key = "test";
    $value = "error";

    $this->request->setError($key, $value);
    $this->assertEqual($this->request->hasError($key), true);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->getError($key), $value);
    $this->assertEqual($this->request->removeError($key), $value);
    $this->assertEqual($this->request->hasError($key), false);
    $this->assertEqual($this->request->hasErrors(), false);
  }
  
  public function test_multiple_errors()
  {
    $key1 = "test1";
    $value_key1_1 = "error1_1";
    $value_key1_2 = "error1_2";
    $key2 = "test 2";
    $value_key2_1 = "error2_1";
    $array_errors = array($key1 => $value_key1_2, $key2 => $value_key2_1);
    $error_names = array($key1, $key2);

    $this->request->setError($key1, $value_key1_1);
    $this->request->setErrors($array_errors);
    $this->assertEqual($this->request->hasError($key1), true);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->getErrorNames(), $error_names);
    $this->assertEqual($this->request->getErrors(), $array_errors);
    $this->assertEqual($this->request->getError($key1), $value_key1_2);
    $this->assertEqual($this->request->removeError($key1), $value_key1_2);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->removeError($key2), $value_key2_1);
    $this->assertEqual($this->request->hasErrors(), false);
  }
  
  public function test_method()
  {
    $this->request->setMethod(sfRequest::GET);
    $this->assertEqual($this->request->getMethod(), sfRequest::GET);
  }

  public function test_parameter()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';
    $this->assertEqual($this->request->hasParameter($name1), false);
    $this->assertEqual($this->request->getParameter($name1, $value1), $value1);
    $this->request->setParameter($name1, $value1);
    $this->assertEqual($this->request->hasParameter($name1), true);
    $this->assertEqual($this->request->getParameter($name1), $value1);
    $this->request->setParameter($name2, $value2, $ns);
    $this->assertEqual($this->request->hasParameter($name2), false);
    $this->assertEqual($this->request->hasParameter($name2, $ns), true);
    $this->assertEqual($this->request->getParameter($name2, '', $ns), $value2);
  }

  public function test_attribute()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';
    $this->assertEqual($this->request->hasAttribute($name1), false);
    $this->assertEqual($this->request->getAttribute($name1, $value1), $value1);
    $this->request->setAttribute($name1, $value1);
    $this->assertEqual($this->request->hasAttribute($name1), true);
    $this->assertEqual($this->request->getAttribute($name1), $value1);
    $this->request->setAttribute($name2, $value2, $ns);
    $this->assertEqual($this->request->hasAttribute($name2), false);
    $this->assertEqual($this->request->hasAttribute($name2, $ns), true);
    $this->assertEqual($this->request->getAttribute($name2, '', $ns), $value2);
  }

  protected function populateVariables($request_uri, $with_layout)
  {
    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    if ($request_uri[0] != '/')
    {
      $request_uri = '/'.$request_uri;
    }

    // add index.php if needed
    if (!strpos($request_uri, '.php'))
    {
      $request_uri = '/index.php'.$request_uri;
    }

    // query string
    $_SERVER['QUERY_STRING'] = '';
    if ($query_string_pos = strpos($request_uri, '?'))
    {
      $_SERVER['QUERY_STRING'] = substr($request_uri, $query_string_pos + 1);
    }
    else
    {
      $query_string_pos = strlen($request_uri);
    }

    // path info
    $_SERVER['PATH_INFO'] = '/';
    $script_pos = strpos($request_uri, '.php') + 5;
    if ($script_pos < $query_string_pos)
    {
      $_SERVER['PATH_INFO'] = '/'.substr($request_uri, $script_pos, $query_string_pos - $script_pos);
    }

    // parse query string
    $params = explode('&', $_SERVER['QUERY_STRING']);
    foreach ($params as $param)
    {
      if (!$param) continue;

      list ($key, $value) = explode('=', $param);
      $_GET[$key] = urldecode($value);
    }

    // change layout
    if (!$with_layout)
    {
      // we simulate an Ajax call to disable layout
      $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }
    else
    {
      unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
  }
}

?>