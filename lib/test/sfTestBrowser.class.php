<?php

require_once(dirname(__FILE__).'/../vendor/lime/lime.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTestBrowser simulates a fake browser which can test a symfony application.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTestBrowser extends sfBrowser
{
  protected static
    $test = null;

  /**
   * Initializes the browser tester instance.
   *
   * @param string $hostname  Hostname
   * @param string $remote    Remote IP address
   * @param array  $options   Options
   */
  public function initialize($hostname = null, $remote = null, $options = array())
  {
    parent::initialize($hostname, $remote, $options);

    $output = isset($options['output']) ? $options['output'] : new lime_output_color();

    if (is_null(self::$test))
    {
      self::$test = new lime_test(null, $output);
    }
  }

  /**
   * Retrieves the lime_test instance.
   *
   * @return lime_test The lime_test instance
   */
  public function test()
  {
    return self::$test;
  }

  /**
   * Retrieves and checks an action.
   *
   * @param  string $module  Module name
   * @param  string $action  Action name
   * @param  string $url     Url
   * @param  string $code    The expected return status code
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function getAndCheck($module, $action, $url = null, $code = 200)
  {
    return $this->
      get(null !== $url ? $url : sprintf('/%s/%s', $module, $action))->
      isStatusCode($code)->
      isRequestParameter('module', $module)->
      isRequestParameter('action', $action)
    ;
  }

  /**
   * Calls a request.
   *
   * @param  string $uri          URI to be invoked
   * @param  string $method       HTTP method used
   * @param  array  $parameters   Additional paramaters
   * @param  bool   $changeStack  If set to false ActionStack is not changed
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    $uri = $this->fixUri($uri);

    $this->test()->comment(sprintf('%s %s', strtolower($method), $uri));

    return parent::call($uri, $method, $parameters, $changeStack);
  }

  /**
   * Simulates the browser back button.
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function back()
  {
    $this->test()->comment('back');

    return parent::back();
  }

  /**
   * Simulates the browser forward button.
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function forward()
  {
    $this->test()->comment('forward');

    return parent::forward();
  }

  /**
   * Tests if the current request has been redirected.
   *
   * @param  bool $boolean  Flag for redirection mode
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isRedirected($boolean = true)
  {
    if ($location = $this->context->getResponse()->getHttpHeader('location'))
    {
      $boolean ? $this->test()->pass(sprintf('page redirected to "%s"', $location)) : $this->test()->fail(sprintf('page redirected to "%s"', $location));
    }
    else
    {
      $boolean ? $this->test()->fail('page redirected') : $this->test()->pass('page not redirected');
    }

    return $this;
  }

  /**
   * Checks that the current response contains a given text.
   *
   * @param  string $uri   Uniform resource identifier
   * @param  string $text  Text in the response
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function check($uri, $text = null)
  {
    $this->get($uri)->isStatusCode();

    if ($text !== null)
    {
      $this->responseContains($text);
    }

    return $this;
  }

  /**
   * Test an status code for the current test browser.
   *
   * @param string Status code to check, default 200
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isStatusCode($statusCode = 200)
  {
    $this->test()->is($this->getResponse()->getStatusCode(), $statusCode, sprintf('status code is "%s"', $statusCode));

    return $this;
  }

  /**
   * Tests whether or not a given string is in the response.
   *
   * @param string Text to check
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function responseContains($text)
  {
    $this->test()->like($this->getResponse()->getContent(), '/'.preg_quote($text, '/').'/', sprintf('response contains "%s"', substr($text, 0, 40)));

    return $this;
  }

  /**
   * Tests whether or not a given key and value exists in the current request.
   *
   * @param string $key
   * @param string $value
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isRequestParameter($key, $value)
  {
    $this->test()->is($this->getRequest()->getParameter($key), $value, sprintf('request parameter "%s" is "%s"', $key, $value));

    return $this;
  }

  /**
   * Checks that the request is forwarded to a given module/action.
   *
   * @param  string $moduleName  The module name
   * @param  string $actionName  The action name
   * @param  mixed  $position    The position in the action stack (default to the last entry)
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isForwardedTo($moduleName, $actionName, $position = 'last')
  {
    $actionStack = $this->context->getActionStack();

    switch ($position)
    {
      case 'first':
        $entry = $actionStack->getFirstEntry();
        break;
      case 'last':
        $entry = $actionStack->getLastEntry();
        break;
      default:
        $entry = $actionStack->getEntry($position);
    }

    $this->test()->is($entry->getModuleName(), $moduleName, sprintf('request is forwarded to the "%s" module (%s)', $moduleName, $position));
    $this->test()->is($entry->getActionName(), $actionName, sprintf('request is forwarded to the "%s" action (%s)', $actionName, $position));

    return $this;
  }

  /**
   * Tests for a response header.
   *
   * @param  string $key
   * @param  string $value
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isResponseHeader($key, $value)
  {
    $headers = explode(', ', $this->getResponse()->getHttpHeader($key));

    $ok = false;

    foreach ($headers as $header)
    {
      if ($header == $value)
      {
        $ok = true;
        break;
      }
    }

    $this->test()->ok($ok, sprintf('response header "%s" is "%s" (%s)', $key, $value, $this->getResponse()->getHttpHeader($key)));

    return $this;
  }

  /**
   * Tests for the user culture.
   *
   * @param  string $culture  The user culture
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isUserCulture($culture)
  {
    $this->test()->is($this->getContext()->getUser()->getCulture(), $culture, sprintf('user culture is "%s"', $culture));

    return $this;
  }

  /**
   * Tests for the request is in the given format.
   *
   * @param  string $format  The request format
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isRequestFormat($format)
  {
    $this->test()->is($this->getContext()->getRequest()->getRequestFormat(), $format, sprintf('request format is "%s"', $format));

    return $this;
  }

  /**
   * Tests that the current response matches a given CSS selector.
   *
   * @param  string $selector  The response selector or a sfDomCssSelector object
   * @param  mixed  $value     Flag for the selector
   * @param  array  $options   Options for the current test
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function checkResponseElement($selector, $value = true, $options = array())
  {
    if (is_object($selector))
    {
      $values = $selector->getValues();
    }
    else
    {
      $values = $this->getResponseDomCssSelector()->matchAll($selector)->getValues();
    }

    if (false === $value)
    {
      $this->test()->is(count($values), 0, sprintf('response selector "%s" does not exist', $selector));
    }
    else if (true === $value)
    {
      $this->test()->cmp_ok(count($values), '>', 0, sprintf('response selector "%s" exists', $selector));
    }
    else if (is_int($value))
    {
      $this->test()->is(count($values), $value, sprintf('response selector "%s" matches "%s" times', $selector, $value));
    }
    else if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      $position = isset($options['position']) ? $options['position'] : 0;
      if ($match[1] == '!')
      {
        $this->test()->unlike(@$values[$position], substr($value, 1), sprintf('response selector "%s" does not match regex "%s"', $selector, substr($value, 1)));
      }
      else
      {
        $this->test()->like(@$values[$position], $value, sprintf('response selector "%s" matches regex "%s"', $selector, $value));
      }
    }
    else
    {
      $position = isset($options['position']) ? $options['position'] : 0;
      $this->test()->is(@$values[$position], $value, sprintf('response selector "%s" matches "%s"', $selector, $value));
    }

    if (isset($options['count']))
    {
      $this->test()->is(count($values), $options['count'], sprintf('response selector "%s" matches "%s" times', $selector, $options['count']));
    }

    return $this;
  }

  /**
   * Tests if an exception is thrown by the latest request.
   *
   * @param  string $class    Class name
   * @param  string $message  Message name
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function throwsException($class = null, $message = null)
  {
    $e = $this->getCurrentException();

    if (null === $e)
    {
      $this->test()->fail('response returns an exception');
    }
    else
    {
      if (null !== $class)
      {
        $this->test()->ok($e instanceof $class, sprintf('response returns an exception of class "%s"', $class));
      }

      if (null !== $message && preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $message, $match))
      {
        if ($match[1] == '!')
        {
          $this->test()->unlike($e->getMessage(), substr($message, 1), sprintf('response exception message does not match regex "%s"', $message));
        }
        else
        {
          $this->test()->like($e->getMessage(), $message, sprintf('response exception message matches regex "%s"', $message));
        }
      }
      else if (null !== $message)
      {
        $this->test()->is($e->getMessage(), $message, sprintf('response exception message matches regex "%s"', $message));
      }
    }

    $this->resetCurrentException();

    return $this;
  }
  
  /**
   * Trigger a test failure if an uncaught exception is present.
   * 
   * @return  bool
   */
  public function checkCurrentExceptionIsEmpty()
  {
    if (false === ($empty = parent::checkCurrentExceptionIsEmpty()))
    {
      $this->test()->fail(sprintf('last request threw an uncatched exception "%s: %s"', get_class($this->getCurrentException()), $this->getCurrentException()->getMessage()));
    }
    
    return $empty;
  }

  /**
   * Tests if the given uri is cached.
   *
   * @param  boolean $boolean      Flag for checking the cache
   * @param  boolean $with_layout  If have or not layout
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isCached($boolean, $with_layout = false)
  {
    return $this->isUriCached($this->context->getRouting()->getCurrentInternalUri(), $boolean, $with_layout);
  }

  /**
   * Tests if the given uri is cached.
   *
   * @param  string  $uri          Uniform resource identifier
   * @param  boolean $boolean      Flag for checking the cache
   * @param  boolean $with_layout  If have or not layout
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isUriCached($uri, $boolean, $with_layout = false)
  {
    $cacheManager = $this->context->getViewCacheManager();

    // check that cache is enabled
    if (!$cacheManager)
    {
      $this->test()->ok(!$boolean, 'cache is disabled');

      return $this;
    }

    if ($uri == $this->context->getRouting()->getCurrentInternalUri())
    {
      $main = true;
      $type = $with_layout ? 'page' : 'action';
    }
    else
    {
      $main = false;
      $type = $uri;
    }

    // check layout configuration
    if ($cacheManager->withLayout($uri) && !$with_layout)
    {
      $this->test()->fail('cache without layout');
      $this->test()->skip('cache is not configured properly', 2);
    }
    else if (!$cacheManager->withLayout($uri) && $with_layout)
    {
      $this->test()->fail('cache with layout');
      $this->test()->skip('cache is not configured properly', 2);
    }
    else
    {
      $this->test()->pass('cache is configured properly');

      // check page is cached
      $ret = $this->test()->is($cacheManager->has($uri), $boolean, sprintf('"%s" %s in cache', $type, $boolean ? 'is' : 'is not'));

      // check that the content is ok in cache
      if ($boolean)
      {
        if (!$ret)
        {
          $this->test()->fail('content in cache is ok');
        }
        else if ($with_layout)
        {
          $response = unserialize($cacheManager->get($uri));
          $content = $response->getContent();
          $this->test()->ok($content == $this->getResponse()->getContent(), 'content in cache is ok');
        }
        else
        {
          $ret = unserialize($cacheManager->get($uri));
          $content = $ret['content'];
          $this->test()->ok(false !== strpos($this->getResponse()->getContent(), $content), 'content in cache is ok');
        }
      }
    }

    return $this;
  }
}

if (!defined('E_RECOVERABLE_ERROR'))
{
  define('E_RECOVERABLE_ERROR', 4096);
}

/**
 * Error handler for the current test browser instance.
 *
 * @param mixed  $errno    Error number
 * @param string $errstr   Error message
 * @param string $errfile  Error file
 * @param mixed  $errline  Error line
 */
function sfTestBrowserErrorHandler($errno, $errstr, $errfile, $errline)
{
  if (($errno & error_reporting()) == 0)
  {
    return;
  }

  $msg = sprintf('PHP send a "%%s" error at %s line %s (%s)', $errfile, $errline, $errstr);
  switch ($errno)
  {
    case E_WARNING:
      throw new Exception(sprintf($msg, 'warning'));
      break;
    case E_NOTICE:
      throw new Exception(sprintf($msg, 'notice'));
      break;
    case E_STRICT:
      throw new Exception(sprintf($msg, 'strict'));
      break;
    case E_RECOVERABLE_ERROR:
      throw new Exception(sprintf($msg, 'catchable'));
      break;
  }
}

set_error_handler('sfTestBrowserErrorHandler');
