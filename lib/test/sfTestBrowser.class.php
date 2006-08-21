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
 * @author     John Christopher <john.christopher@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTestBrowser
{
  private static
    $current_context = null;

  private
    $presentation = '',
    $redirects = null;

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

  public function get($request_uri = '/', $with_layout = true, $followRedirects = false)
  {
    $this->populateGetVariables($request_uri, $with_layout);
    $context = $this->initRequest();
    $html = $this->getContent();

    $redirectUri = self::$current_context->getController()->getRedirectedUri();
    $this->closeRequest();

    $html = $this->handleRedirect($html, $redirectUri, $with_layout, $followRedirects);

    return $html;
  }

  public function post($action_uri, $params = array(), $with_layout = true, $followRedirects = false)
  {
    $this->populatePostVariables($action_uri, $params, $with_layout);
    $context = $this->initRequest();

    $html = $this->getContent();

    $redirectUri = self::$current_context->getController()->getRedirectedUri();
    $this->closeRequest();

    $html = $this->handleRedirect($html, $redirectUri, $with_layout, $followRedirects);

    return $html;
  }

  public function initRequest()
  {
    if (self::$current_context)
    {
      throw new sfException('a request is already active');
    }

     // launch request via controller
    $context = sfContext::getInstance();
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

    $this->redirects = null;
  }

  /**
   * Asserts if a redirect was issued or not. If a redirect URL(s) is provided, will only assert
   * for that/those URL(s) otherwise, will return true if any redirect was issued.
   * 
   */
  public function assertRedirect($redirectUrl = null)
  {
    return $this->checkRedirect($redirectUrl);
  }

  protected function populateGetVariables($request_uri, $with_layout, $request_method = 'GET')
  {
    $_GET  = array();
    $_POST = array();

    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = $request_method;
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $request_uri = $this->checkRequestUri($request_uri);

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
      if (!$param)
      {
        continue;
      }

      list ($key, $value) = explode('=', $param);

      $_GET[$key] = urldecode($value);
    }

    $this->changeLayout($with_layout);
  }

  protected function populatePostVariables($request_uri, $params, $with_layout)
  {
    array_walk_recursive($params, array('sfTestBrowser', 'recursiveUrlDecodeCallback'));

    $this->populateGetVariables($request_uri, $with_layout, 'POST');

    foreach ($params as $key => $value)
    {
      $_POST[$key] = $value;
    }
  }

  private static function recursiveUrlDecodeCallback(&$value)
  {
    $value = urldecode($value);
  }

  private function checkRequestUri($request_uri)
  {
    if ($request_uri[0] != '/')
    {
      $request_uri = '/'.$request_uri;
    }

    // add index.php if needed
    if (!strpos($request_uri, '.php'))
    {
      $request_uri = '/index.php'.$request_uri;
    }

    return $request_uri;
  }

  private function changeLayout($with_layout)
  {
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

  private function handleRedirect($html, $redirectUri, $with_layout, $followRedirect)
  {
    if($redirectUri)
    {
      if($this->redirects)
      {
      array_push($this->redirects, $redirectUri);
      }
      else
      {
        $this->redirects = array();
        array_push($this->redirects, $redirectUri);
      }

      if ($followRedirect)
      {
        $html = $this->get($redirectUri, $with_layout);
      }
    }

    return $html;
  }

  private function checkRedirect($redirectUrl)
  {
    $redirectFound = true;

    if($this->redirects)
    {
      if($redirectUrl)
      {
        if(is_array($redirectUrl))
        {
          foreach($redirectUrl As $redirect)
          {
            if(!in_array($redirect, $this->redirects))
            {
              $redirectFound = false;
              break;
            }
          }
        }
        else //redirectUrl is a single URL
        {
          if (!in_array($redirectUrl, $this->redirects))
          {
            $redirectFound = false;
          }
        }
      }
    }
    else
    {
      $redirectFound = false;
    }

    return $redirectFound;
  }
}
