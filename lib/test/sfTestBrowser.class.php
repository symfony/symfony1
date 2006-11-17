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
  protected
    $test = null;

  public function initialize($hostname = null, $remote = null, $options = array())
  {
    parent::initialize($hostname, $remote, $options);

    $output = isset($options['output']) ? $options['output'] : new lime_output_color();

    $this->test = new lime_test(null, $output);
  }

  public function test()
  {
    return $this->test;
  }

  public function getAndCheck($module, $action, $url = null, $code = 200)
  {
    return $this->
      get(null !== null ? $url : sprintf('/%s/%s', $module, $action))->
      isStatusCode($code)->
      isRequestParameter('module', $module)->
      isRequestParameter('action', $action)
    ;
  }

  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    $uri = $this->fixUri($uri);

    $this->test->comment(sprintf('%s %s', strtolower($method), $uri));

    return parent::call($uri, $method, $parameters, $changeStack);
  }

  public function back()
  {
    $this->test->comment('back');

    return parent::back();
  }

  public function forward()
  {
    $this->test->comment('forward');

    return parent::forward();
  }

  public function isRedirected($boolean = true)
  {
    if ($location = $this->getContext()->getResponse()->getHttpHeader('location'))
    {
      $boolean ? $this->test->pass(sprintf('page redirected to "%s"', $location)) : $this->test->fail(sprintf('page redirected to "%s"', $location));
    }
    else
    {
      $boolean ? $this->test->fail('page redirected') : $this->test->pass('page not redirected');
    }

    return $this;
  }

  public function check($uri, $text = null)
  {
    $this->get($uri)->isStatusCode();

    if ($text !== null)
    {
      $this->responseContains($text);
    }

    return $this;
  }

  public function isStatusCode($statusCode = 200)
  {
    $this->test->is($this->getResponse()->getStatusCode(), $statusCode, sprintf('status code is "%s"', $statusCode));

    return $this;
  }

  public function responseContains($text)
  {
    $this->test->like($this->getResponse()->getContent(), '/'.preg_quote($text, '/').'/', sprintf('response contains "%s"', substr($text, 0, 40)));

    return $this;
  }

  public function isRequestParameter($key, $value)
  {
    $this->test->is($this->getRequest()->getParameter($key), $value, sprintf('request parameter "%s" is "%s"', $key, $value));

    return $this;
  }

  public function isResponseHeader($key, $value)
  {
    $headers = $this->getResponse()->getHttpHeader($key);

    $ok = false;
    foreach ($headers as $header)
    {
      if ($header == $value)
      {
        $ok = true;
        break;
      }
    }

    $this->test->ok($ok, sprintf('response header "%s" is "%s"', $key, $value));

    return $this;
  }

  public function checkResponseElement($selector, $value = true, $options = array())
  {
    $texts = $this->getResponseDomCssSelector()->getTexts($selector);

    if (false === $value)
    {
      $this->test->is(count($texts), 0, sprintf('response selector "%s" does not exist', $selector));
    }
    else if (true === $value)
    {
      $this->test->cmp_ok(count($texts), '>', 0, sprintf('response selector "%s" exists', $selector));
    }
    else if (is_int($value))
    {
      $this->test->is(count($texts), $value, sprintf('response selector "%s" matches "%s" times', $selector, $value));
    }
    else if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      $position = isset($options['position']) ? $options['position'] : 0;
      if ($match[1] == '!')
      {
        $this->test->unlike(@$texts[$position], substr($value, 1), sprintf('response selector "%s" does not match regex "%s"', $selector, substr($value, 1)));
      }
      else
      {
        $this->test->like(@$texts[$position], $value, sprintf('response selector "%s" matches regex "%s"', $selector, $value));
      }
    }
    else
    {
      $position = isset($options['position']) ? $options['position'] : 0;
      $this->test->is(@$texts[$position], $value, sprintf('response selector "%s" matches "%s"', $selector, $value));
    }

    if (isset($options['count']))
    {
      $this->test->is(count($texts), $options['count'], sprintf('response selector "%s" matches "%s" times', $selector, $options['count']));
    }

    return $this;
  }

  public function isCached($boolean, $with_layout = false)
  {
    return $this->isUriCached(sfRouting::getInstance()->getCurrentInternalUri(), $boolean, $with_layout);
  }

  public function isUriCached($uri, $boolean, $with_layout = false)
  {
    $cacheManager = $this->getContext()->getViewCacheManager();

    // check that cache is activated
    if (!$cacheManager)
    {
      $this->test->ok(!$boolean, 'cache is disabled');

      return $this;
    }

    if ($uri == sfRouting::getInstance()->getCurrentInternalUri())
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
      $this->test->fail('cache without layout');
      $this->test->skip('cache is not configured properly', 2);
    }
    else if (!$cacheManager->withLayout($uri) && $with_layout)
    {
      $this->test->fail('cache with layout');
      $this->test->skip('cache is not configured properly', 2);
    }
    else
    {
      $this->test->pass('cache is configured properly');

      // check page is cached
      $ret = $this->test->is($cacheManager->has($uri), $boolean, sprintf('"%s" %s in cache', $type, $boolean ? 'is' : 'is not'));

      // check that the content is ok in cache
      if ($boolean)
      {
        if (!$ret)
        {
          $this->test->fail('content in cache is ok');
        }
        else if ($with_layout)
        {
          $response = unserialize($cacheManager->get($uri));
          $content = $response->getContent();
          $this->test->is($content, $this->getResponse()->getContent(), 'content in cache is ok');
        }
        else if (true === $main)
        {
          $ret = unserialize($cacheManager->get($uri));
          $content = $ret['content'];
          $this->test->ok(false !== strpos($this->getResponse()->getContent(), $content), 'content in cache is ok');
        }
        else
        {
          $content = $cacheManager->get($uri);
          $this->test->ok(false !== strpos($this->getResponse()->getContent(), $content), 'content in cache is ok');
        }
      }
    }

    return $this;
  }
}

function sfTestBrowserErrorHandler($errno, $errstr, $errfile, $errline)
{
  if (($errno & error_reporting()) == 0)
  {
    return;
  }

  $msg = sprintf('PHP send a "%s" error at %s line %s (%s)', '%s', $errfile, $errline, $errstr);
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
  }
}

set_error_handler('sfTestBrowserErrorHandler');
