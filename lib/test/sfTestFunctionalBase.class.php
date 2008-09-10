<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTestFunctional tests an application by using a browser simulator.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfTestFunctionalBase
{
  protected
    $browser = null;

  protected static
    $test = null;

  /**
   * Initializes the browser tester instance.
   *
   * @param sfBrowserBase $browser A sfBrowserBase instance
   * @param lime_test     $lime    A lime instance
   */
  public function __construct(sfBrowserBase $browser, lime_test $lime = null)
  {
    $this->browser = $browser;

    if (is_null(self::$test))
    {
      self::$test = !is_null($lime) ? $lime : new lime_test(null, new lime_output_color());
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
   * Gets a uri.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return sfBrowser
   */
  public function get($uri, $parameters = array(), $changeStack = true)
  {
    return $this->call($uri, 'get', $parameters);
  }

  /**
   * Posts a uri.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return sfBrowser
   */
  public function post($uri, $parameters = array(), $changeStack = true)
  {
    return $this->call($uri, 'post', $parameters);
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
    $uri = $this->browser->fixUri($uri);

    $this->test()->comment(sprintf('%s %s', strtolower($method), $uri));

    $this->browser->call($uri, $method, $parameters, $changeStack);

    return $this;
  }

  /**
   * Simulates the browser back button.
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function back()
  {
    $this->test()->comment('back');

    return $this->browser->back();
  }

  /**
   * Simulates the browser forward button.
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function forward()
  {
    $this->test()->comment('forward');

    return $this->browser->forward();
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
    if ($location = $this->getResponse()->getHttpHeader('location'))
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
   * Tests if the current HTTP method matches the given one
   *
   * @param  string  $method  The HTTP method name
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isRequestMethod($method)
  {
    $this->test()->ok($this->getRequest()->isMethod($method), sprintf('request method is "%s"', strtoupper($method)));

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
    $this->test()->is($this->getUser()->getCulture(), $culture, sprintf('user culture is "%s"', $culture));

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
    $this->test()->is($this->getRequest()->getRequestFormat(), $format, sprintf('request format is "%s"', $format));

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
        $this->test()->is($e->getMessage(), $message, sprintf('response exception message is "%s"', $message));
      }
    }

    $this->resetCurrentException();

    return $this;
  }

  /**
   * Triggers a test failure if an uncaught exception is present.
   *
   * @return  bool
   */
  public function checkCurrentExceptionIsEmpty()
  {
    if (false === ($empty = $this->browser->checkCurrentExceptionIsEmpty()))
    {
      $this->test()->fail(sprintf('last request threw an uncaught exception "%s: %s"', get_class($this->getCurrentException()), $this->getCurrentException()->getMessage()));
    }

    return $empty;
  }

  /**
   * Checks if a cookie exists.
   *
   * @param string  $name   The cookie name
   * @param Boolean $exists Whether the cookie must exist or not
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function hasCookie($name, $exists = true)
  {
    if (!array_key_exists($name, $_COOKIE))
    {
      if ($exists)
      {
        $this->test()->fail(sprintf('cookie "%s" exist.', $name));
      }
      else
      {
        $this->test()->pass(sprintf('cookie "%s" does not exist.', $name));
      }

      return $this;
    }

    if ($exists)
    {
      $this->test()->pass(sprintf('cookie "%s" exists.', $name));
    }
    else
    {
      $this->test()->fail(sprintf('cookie "%s" does not exist.', $name));
    }

    return $this;
  }

  /**
   * Checks the value of a cookie.
   *
   * @param string $name   The cookie name
   * @param mixed  $value  The expected value
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isCookie($name, $value)
  {
    if (!array_key_exists($name, $_COOKIE))
    {
      $this->test()->fail(sprintf('cookie "%s" does not exist.', $name));

      return $this;
    }

    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      if ($match[1] == '!')
      {
        $this->test()->unlike($_COOKIE[$name], substr($value, 1), sprintf('cookie "%s" content does not match regex "%s"', $name, $value));
      }
      else
      {
        $this->test()->like($_COOKIE[$name], $value, sprintf('cookie "%s" content matches regex "%s"', $name, $value));
      }
    }
    else if (null !== $message)
    {
      $this->test()->is($_COOKIE[$name], $value, sprintf('cookie "%s" content is ok', $name));
    }

    return $this;
  }

  public function __call($method, $arguments)
  {
    $retval = call_user_func_array(array($this->browser, $method), $arguments);

    // fix the fluent interface
    return $retval === $this->browser ? $this : $retval;
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
