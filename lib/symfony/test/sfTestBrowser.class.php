<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextBrowser simulates a fake browser which can surf a symfony application.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTestBrowser
{
  /*

  FIXME/TODO:
    - POST support
    - redirect support?

  */
  private
    $presentation = '';

  private static
    $current_context = null;

  public function initialize ($hostname = null)
  {
    // setup our fake environment
    $_SERVER['HTTP_HOST'] = ($hostname ? $hostname : sfConfig::get('sf_app').'-'.sfConfig::get('sf_environment'));
    $_SERVER['HTTP_USER_AGENT'] = 'PHP5/CLI';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    // we set a session id (fake cookie / persistence)
    $_SERVER['session_id'] = md5(uniqid(rand(), true));

    sfConfig::set('sf_path_info_array', 'SERVER');

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));
  }

  public function get($request_uri = '/', $with_layout = true)
  {
    $context = $this->initRequest($request_uri, $with_layout);
    $html = $this->getContent();
    $this->closeRequest();

    return $html;
  }

  public function initRequest($request_uri = '/', $with_layout = true)
  {
    if (self::$current_context)
    {
      throw new sfException('a request is already active');
    }

    $this->populateVariables($request_uri, $with_layout);

    // launch request via controller
    $context    = sfContext::getInstance();
    $controller = $context->getController();
    $request    = $context->getRequest();

    $request->getParameterHolder()->clear();
    $request->initialize($context);

    ob_start();
    $controller->dispatch();
    $this->presentation = ob_get_clean();

    // manually shutdown user to save current session data
    $context->getUser()->shutdown();

    self::$current_context = $context;

    return $context;
  }

  public function getContext()
  {
    return self::$current_context;
  }

  public function getContent()
  {
    if (!self::$current_context)
    {
      throw new sfException('a request must be active');
    }

    return $this->presentation;
  }

  public function closeRequest()
  {
    if (!self::$current_context)
    {
      throw new sfException('a request must be active');
    }

    // clean state
    self::$current_context->shutdown();
    self::$current_context = null;
    sfContext::removeInstance();
  }

  public function shutdown()
  {
    // we remove all session data
    sfToolkit::clearDirectory(sfConfig::get('sf_test_cache_dir'));
  }

  protected function populateVariables($request_uri, $with_layout)
  {
    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
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

    $_SERVER['REQUEST_URI'] = $request_uri;

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