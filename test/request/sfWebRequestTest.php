<?php

require_once 'symfony/request/sfRequest.class.php';
require_once 'symfony/request/sfWebRequest.class.php';

Mock::generate('sfContext');

class sfWebRequestTest extends UnitTestCase
{
  private $context;
  private $request;

  public function SetUp()
  {
    sfRouting::getInstance()->clearRoutes();

    @define('SF_STATS', false);
    @define('SF_PATH_INFO_ARRAY', 'SERVER');
    @define('SF_PATH_INFO_KEY', 'PATH_INFO');
    $this->populateVariables('/', true);

    $this->context = new MockSfContext($this);
    $this->request = sfRequest::newInstance('sfWebRequest');
    $this->request->initialize($this->context);
  }

  public function test_pathinfo()
  {
//    $this->populateVariables('http://domain.com/index.php/test/value', true);
//    $this->assertEqual($this->request->getPathInfo(), '/test/value');
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