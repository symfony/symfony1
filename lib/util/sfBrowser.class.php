<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBrowser simulates a fake browser which can surf a symfony application.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfBrowser
{
  protected
    $context            = null,
    $hostname           = null,
    $remote             = null,
    $dom                = null,
    $stack              = array(),
    $stackPosition      = -1,
    $cookieJar          = array(),
    $fields             = array(),
    $files              = array(),
    $vars               = array(),
    $defaultServerArray = array(),
    $headers            = array(),
    $currentException   = null;

  /**
   * Class constructor.
   *
   * @param string Hostname to browse
   * @param string Remote address to spook
   * @param array  Options for sfBrowser
   *
   * @return void
   */
  public function __construct($hostname = null, $remote = null, $options = array())
  {
    $this->initialize($hostname, $remote, $options);
  }

  /**
   * Initializes sfBrowser - sets up environment
   *
   * @param string Hostname to browse
   * @param string Remote address to spook
   * @param array  Options for sfBrowser
   *
   * @return void
   */
  public function initialize($hostname = null, $remote = null, $options = array())
  {
    unset($_SERVER['argv']);
    unset($_SERVER['argc']);

    // setup our fake environment
    $this->hostname = $hostname;
    $this->remote   = $remote;

    sfConfig::set('sf_path_info_array', 'SERVER');
    sfConfig::set('sf_test', true);

    // we set a session id (fake cookie / persistence)
    $this->newSession();

    // store default global $_SERVER array
    $this->defaultServerArray = $_SERVER;

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));
  }

  /**
   * Sets variable name
   *
   * @param string The variable name
   * @param mixed  The value
   *
   * @return sfBrowser
   */
  public function setVar($name, $value)
  {
    $this->vars[$name] = $value;

    return $this;
  }

  /**
   * Sets a HTTP header for the very next request.
   *
   * @param string The header name
   * @param string The header value
   */
  public function setHttpHeader($header, $value)
  {
    $this->headers[$header] = $value;

    return $this;
  }

  /**
   * Sets username and password for simulating http authentication.
   *
   * @param string The username
   * @param string The password
   *
   * @return sfBrowser
   */
  public function setAuth($username, $password)
  {
    $this->vars['PHP_AUTH_USER'] = $username;
    $this->vars['PHP_AUTH_PW']   = $password;

    return $this;
  }

  /**
   * Gets a uri.
   *
   * @param string The URI to fetch
   * @param array  The Request parameters
   *
   * @return sfBrowser
   */
  public function get($uri, $parameters = array())
  {
    return $this->call($uri, 'get', $parameters);
  }

  /**
   * Posts a uri.
   *
   * @param string The URI to fetch
   * @param array  The Request parameters
   *
   * @return sfBrowser
   */
  public function post($uri, $parameters = array())
  {
    return $this->call($uri, 'post', $parameters);
  }

  /**
   * Calls a request to a uri.
   *
   * @param string The URI to fetch
   * @param string The request method
   * @param array  The Request parameters
   * @param boolean Change the browser history stack?
   *
   * @return sfBrowser
   */
  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    // check that the previous call() hasn't returned an uncatched exception
    $this->checkCurrentExceptionIsEmpty();

    $uri = $this->fixUri($uri);

    // add uri to the stack
    if ($changeStack)
    {
      $this->stack = array_slice($this->stack, 0, $this->stackPosition + 1);
      $this->stack[] = array(
        'uri'        => $uri,
        'method'     => $method,
        'parameters' => $parameters,
      );
      $this->stackPosition = count($this->stack) - 1;
    }

    list($path, $query_string) = false !== ($pos = strpos($uri, '?')) ? array(substr($uri, 0, $pos), substr($uri, $pos + 1)) : array($uri, '');
    $query_string = html_entity_decode($query_string);

    // remove anchor
    $path = preg_replace('/#.*/', '', $path);

    // removes all fields from previous request
    $this->fields = array();

    // prepare the request object
    $_SERVER = $this->defaultServerArray;
    $_SERVER['HTTP_HOST']       = $this->hostname ? $this->hostname : sfConfig::get('sf_app').'-'.sfConfig::get('sf_environment');
    $_SERVER['SERVER_NAME']     = $_SERVER['HTTP_HOST'];
    $_SERVER['SERVER_PORT']     = 80;
    $_SERVER['HTTP_USER_AGENT'] = 'PHP5/CLI';
    $_SERVER['REMOTE_ADDR']     = $this->remote ? $this->remote : '127.0.0.1';
    $_SERVER['REQUEST_METHOD']  = strtoupper($method);
    $_SERVER['PATH_INFO']       = $path;
    $_SERVER['REQUEST_URI']     = '/index.php'.$uri;
    $_SERVER['SCRIPT_NAME']     = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = '/index.php';
    $_SERVER['QUERY_STRING']    = $query_string;
    foreach ($this->vars as $key => $value)
    {
      $_SERVER[strtoupper($key)] = $value;
    }

    foreach ($this->headers as $header => $value)
    {
      $_SERVER['HTTP_'.strtoupper(str_replace('-', '_', $header))] = $value;
    }
    $this->headers = array();

    // request parameters
    $_GET = $_POST = array();
    if (strtoupper($method) == 'POST')
    {
      $_POST = $parameters;
    }
    if (strtoupper($method) == 'GET')
    {
      $_GET  = $parameters;
    }

    // handle input type="file" fields
    if (count($this->files))
    {
      $_FILES = $this->files;
    }
    $this->files = array();

    parse_str($query_string, $qs);
    if (is_array($qs))
    {
      $_GET = array_merge($qs, $_GET);
    }

    // restore cookies
    $_COOKIE = array();
    foreach ($this->cookieJar as $name => $cookie)
    {
      $_COOKIE[$name] = $cookie['value'];
    }

    ob_start();

    // recycle our context object
    $this->context = $this->getContext(true);

    // launch request via controller
    $controller = $this->context->getController();
    $request    = $this->context->getRequest();
    $response   = $this->context->getResponse();

    // we register a fake rendering filter
    sfConfig::set('sf_rendering_filter', array('sfFakeRenderingFilter', null));

    $this->currentException = null;

    // dispatch our request
    $controller->dispatch();

    $retval = ob_get_clean();

    // append retval to the response content
    $response->setContent($retval);

    // manually shutdown user to save current session data
    $this->context->getUser()->shutdown();
    $this->context->getStorage()->shutdown();

    // save cookies
    $this->cookieJar = array();
    foreach ($response->getCookies() as $name => $cookie)
    {
      // FIXME: deal with expire, path, secure, ...
      $this->cookieJar[$name] = $cookie;
    }

    // support for the ETag header
    if ($etag = $this->context->getResponse()->getHttpHeader('Etag'))
    {
      $this->vars['HTTP_IF_NONE_MATCH'] = $etag;
    }
    else
    {
      unset($this->vars['HTTP_IF_NONE_MATCH']);
    }

    // support for the last modified header
    if ($lastModified = $this->context->getResponse()->getHttpHeader('Last-Modified'))
    {
      $this->vars['HTTP_IF_MODIFIED_SINCE'] = $lastModified;
    }
    else
    {
      unset($this->vars['HTTP_IF_MODIFIED_SINCE']);
    }

    // for HTML/XML content, create a DOM and sfDomCssSelector objects for the response content
    if (preg_match('/(x|ht)ml/i', $response->getContentType()))
    {
      $this->dom = new DomDocument('1.0', sfConfig::get('sf_charset'));
      $this->dom->validateOnParse = true;
      @$this->dom->loadHTML($response->getContent());
      $this->domCssSelector = new sfDomCssSelector($this->dom);
    }
    else
    {
      $this->dom = null;
      $this->domCssSelector = null;
    }

    return $this;
  }

  /**
   * Go back in the browser history stack.
   *
   * @return sfBrowser
   */
  public function back()
  {
    if ($this->stackPosition < 1)
    {
      throw new sfException('You are already on the first page.');
    }

    --$this->stackPosition;
    return $this->call($this->stack[$this->stackPosition]['uri'], $this->stack[$this->stackPosition]['method'], $this->stack[$this->stackPosition]['parameters'], false);
  }

  /**
   * Go forward in the browser history stack.
   *
   * @return sfBrowser
   */
  public function forward()
  {
    if ($this->stackPosition > count($this->stack) - 2)
    {
      throw new sfException('You are already on the last page.');
    }

    ++$this->stackPosition;
    return $this->call($this->stack[$this->stackPosition]['uri'], $this->stack[$this->stackPosition]['method'], $this->stack[$this->stackPosition]['parameters'], false);
  }

  /**
   * Reload the current browser.
   *
   * @return sfBrowser
   */
  public function reload()
  {
    if (-1 == $this->stackPosition)
    {
      throw new sfException('No page to reload.');
    }

    return $this->call($this->stack[$this->stackPosition]['uri'], $this->stack[$this->stackPosition]['method'], $this->stack[$this->stackPosition]['parameters'], false);
  }

  /**
   * Get response dom css selector.
   *
   * @return sfDomCssSelector
   */
  public function getResponseDomCssSelector()
  {
    if (is_null($this->dom))
    {
      throw new sfException('The DOM is not accessible because the browser response content type is not HTML.');
    }

    return $this->domCssSelector;
  }

  /**
   * Get response dom.
   *
   * @return sfDomCssSelector
   */
  public function getResponseDom()
  {
    if (is_null($this->dom))
    {
      throw new sfException('The DOM is not accessible because the browser response content type is not HTML.');
    }

    return $this->dom;
  }

  /**
   * Returns the current application context.
   *
   * @param  Boolean true to force context reload, false otherwise
   *
   * @return sfContext
   */
  public function getContext($forceReload = false)
  {
    if (is_null($this->context) || $forceReload)
    {
      if (!is_null($this->context))
      {
        $currentConfiguration = $this->context->getConfiguration();
        $configuration = ProjectConfiguration::getApplicationConfiguration($currentConfiguration->getApplication(), $currentConfiguration->getEnvironment(), $currentConfiguration->isDebug());
        $this->context = sfContext::createInstance($configuration);
      }
      else
      {
        $this->context = sfContext::getInstance();
        $this->context->initialize($this->context->getConfiguration());
      }

      $this->context->getEventDispatcher()->connect('application.throw_exception', array($this, 'ListenToException'));
      unset($currentConfiguration);
    }

    return $this->context;
  }

  /**
   * Gets response.
   *
   * @return sfWebResponse
   */
  public function getResponse()
  {
    return $this->context->getResponse();
  }

  /**
   * Gets request.
   *
   * @return sfWebRequest
   */
  public function getRequest()
  {
    return $this->context->getRequest();
  }

  /**
   * Gets current exception.
   *
   * @return sfException
   */
  public function getCurrentException()
  {
    return $this->currentException;
  }

  /**
   * Resets the current exception.
   */
  public function resetCurrentException()
  {
    $this->currentException = null;
  }

  /**
   * Test for an uncaught exception.
   * 
   * @return  boolean
   */
  public function checkCurrentExceptionIsEmpty()
  {
    return is_null($this->getCurrentException()) || $this->getCurrentException() instanceof sfError404Exception;
  }

  /**
   * Follow redirects?
   *
   * @throws sfException If request was not a redirect
   *
   * @return sfBrowser
   */
  public function followRedirect()
  {
    if (null === $this->context->getResponse()->getHttpHeader('Location'))
    {
      throw new sfException('The request was not redirected.');
    }

    return $this->get($this->context->getResponse()->getHttpHeader('Location'));
  }

  /**
   * Sets a form field in the browser.
   *
   * @param string The field name
   * @param string The field value
   *
   * @return sfBrowser
   */
  public function setField($name, $value)
  {
    // as we don't know yet the form, just store name/value pairs
    $this->parseArgumentAsArray($name, $value, $this->fields);

    return $this;
  }

  /**
   * Simulates a click on a link or button.
   *
   * @param string $name The link or button text
   * @param array $arguments
   *
   * @return sfBrowser
   */
  public function click($name, $arguments = array())
  {
    $dom = $this->getResponseDom();

    if (!$dom)
    {
      throw new sfException('Cannot click because there is no current page in the browser.');
    }

    $xpath = new DomXpath($dom);

    // text link
    if ($link = $xpath->query(sprintf('//a[.="%s"]', $name))->item(0))
    {
      return $this->get($link->getAttribute('href'));
    }

    // image link
    if ($link = $xpath->query(sprintf('//a/img[@alt="%s"]/ancestor::a', $name))->item(0))
    {
      return $this->get($link->getAttribute('href'));
    }

    // form
    if (!$form = $xpath->query(sprintf('//input[((@type="submit" or @type="button") and @value="%s") or (@type="image" and @alt="%s")]/ancestor::form', $name, $name))->item(0))
    {
      if (!$form = $xpath->query(sprintf('//button[.="%s" or @id="%s" or @name="%s"]/ancestor::form', $name, $name, $name))->item(0))
      {
        throw new sfException(sprintf('Cannot find the "%s" link or button.', $name));
      }
    }

    // form attributes
    $url = $form->getAttribute('action');
    $method = $form->getAttribute('method') ? strtolower($form->getAttribute('method')) : 'get';

    // merge form default values and arguments
    $defaults = array();
    $arguments = sfToolkit::arrayDeepMerge($this->fields, $arguments);

    foreach ($xpath->query('descendant::input | descendant::textarea | descendant::select', $form) as $element)
    {
      $elementName = $element->getAttribute('name');
      $nodeName    = $element->nodeName;
      $value       = null;

      if ($nodeName == 'input' && ($element->getAttribute('type') == 'checkbox' || $element->getAttribute('type') == 'radio'))
      {
        if ($element->getAttribute('checked'))
        {
          $value = $element->getAttribute('value');
        }
      }
      else if ($nodeName == 'input' && $element->getAttribute('type') == 'file')
      {
        $ph = new sfParameterHolder();
        $ph->add($arguments);

        $filename = $ph->get($elementName, '');

        if (is_readable($filename))
        {
          $fileError = UPLOAD_ERR_OK;
          $fileSize = filesize($filename);
        }
        else
        {
          $fileError = UPLOAD_ERR_NO_FILE;
          $fileSize = 0;
        }

        $ph->remove($elementName);
        $arguments = $ph->getAll();

        $this->parseArgumentAsArray($elementName, array('name' => basename($filename), 'type' => '', 'tmp_name' => $filename, 'error' => $fileError, 'size' => $fileSize), $this->files);
      }
      else if (
        $nodeName == 'input'
        &&
        (($element->getAttribute('type') != 'submit' && $element->getAttribute('type') != 'button') || $element->getAttribute('value') == $name)
        &&
        ($element->getAttribute('type') != 'image' || $element->getAttribute('alt') == $name)
      )
      {
        $value = $element->getAttribute('value');
      }
      else if ($nodeName == 'textarea')
      {
        $value = '';
        foreach ($element->childNodes as $el)
        {
          $value .= $dom->saveXML($el);
        }
      }
      else if ($nodeName == 'select')
      {
        if ($multiple = $element->hasAttribute('multiple'))
        {
          $elementName = str_replace('[]', '', $elementName);
          $value = array();
        }
        else
        {
          $value = null;
        }

        $found = false;
        foreach ($xpath->query('descendant::option', $element) as $option)
        {
          if ($option->getAttribute('selected'))
          {
            $found = true;
            if ($multiple)
            {
              $value[] = $option->getAttribute('value');
            }
            else
            {
              $value = $option->getAttribute('value');
            }
          }
        }

        // if no option is selected and if it is a simple select box, take the first option as the value
        if (!$found && !$multiple)
        {
          $value = $xpath->query('descendant::option', $element)->item(0)->getAttribute('value');
        }
      }

      if (null !== $value)
      {
        $this->parseArgumentAsArray($elementName, $value, $defaults);
      }
    }

    // create request parameters
    $arguments = sfToolkit::arrayDeepMerge($defaults, $arguments);
    if ('post' == $method)
    {
      return $this->post($url, $arguments);
    }
    else
    {
      $query_string = http_build_query($arguments, null, '&');
      $sep = false === strpos($url, '?') ? '?' : '&';

      return $this->get($url.($query_string ? $sep.$query_string : ''));
    }
  }

  /**
   * Parses arguments as array
   *
   * @param string The argument name
   * @param string The argument value
   * @param array  $vars
   */
  protected function parseArgumentAsArray($name, $value, &$vars)
  {
    if (false !== $pos = strpos($name, '['))
    {
      $var = &$vars;
      $tmps = array_filter(preg_split('/(\[ | \[\] | \])/x', $name));
      foreach ($tmps as $tmp)
      {
        $var = &$var[$tmp];
      }
      if ($var)
      {
        if (!is_array($var))
        {
          $var = array($var);
        }
        $var[] = $value;
      }
      else
      {
        $var = $value;
      }
    }
    else
    {
      $vars[$name] = $value;
    }
  }

  /**
   * Reset browser to original state
   *
   * @return sfBrowser
   */
  public function restart()
  {
    $this->newSession();
    $this->cookieJar     = array();
    $this->stack         = array();
    $this->fields        = array();
    $this->vars          = array();
    $this->dom           = null;
    $this->stackPosition = -1;

    return $this;
  }

  /**
   * Shutdown function to clean up and remove sessions
   *
   * @return void
   */
  public function shutdown()
  {
    $this->checkCurrentExceptionIsEmpty();

    // we remove all session data
    sfToolkit::clearDirectory(sfConfig::get('sf_test_cache_dir').'/sessions');
  }

  /**
   * Fixes uri removing # declarations and front controller.
   *
   * @param string The URI to fix
   * @return string The fixed uri
   */
  protected function fixUri($uri)
  {
    // remove absolute information if needed (to be able to do follow redirects, click on links, ...)
    if (0 === strpos($uri, 'http'))
    {
      // detect secure request
      if (0 === strpos($uri, 'https'))
      {
        $this->defaultServerArray['HTTPS'] = 'on';
      }
      else
      {
        unset($this->defaultServerArray['HTTPS']);
      }

      $uri = substr($uri, strpos($uri, 'index.php') + strlen('index.php'));
    }
    $uri = str_replace('/index.php', '', $uri);

    // # as a uri
    if ($uri && '#' == $uri[0])
    {
      $uri = $this->stack[$this->stackPosition]['uri'].$uri;
    }

    return $uri;
  }

  /**
   * Creates a new session in the browser.
   *
   * @return void
   */
  protected function newSession()
  {
    $_SERVER['session_id'] = md5(uniqid(rand(), true));
  }

  /**
   * Listener for exceptions
   *
   * @param sfEvent The event to handle
   *
   * @return void
   */
  public function listenToException(sfEvent $event)
  {
    $this->currentException = $event->getSubject();
  }
}

class sfFakeRenderingFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $filterChain->execute();

    $this->context->getResponse()->sendContent();
  }
}
