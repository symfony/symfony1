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

  public function initialize($output = null)
  {
    parent::initialize();

    if (null === $output)
    {
      $output = new lime_output_color();
    }

    $this->test = new lime_test(null, $output);
  }

  public function test()
  {
    return $this->test;
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

  public function isRedirected()
  {
    $locations = $this->getContext()->getResponse()->getHttpHeader('location');

    $this->test->ok($locations[0], sprintf('page redirected to "%s"', $locations[0]));

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
      $this->test->is(count($this->getResponseDomCssSelector()->getElements($selector)), 0, sprintf('response selector "%s" does not exist', $selector));
    }
    else if (true === $value)
    {
      $this->test->cmp_ok(count($this->getResponseDomCssSelector()->getElements($selector)), '>', 0, sprintf('response selector "%s" exists', $selector));
    }
    else if (is_int($value))
    {
      $this->test->is(count($this->getResponseDomCssSelector()->getElements($selector)), $value, sprintf('response selector "%s" matches "%s" times', $selector, $value));
    }
    else if (preg_match('/^(!)?(.).+?\\2[ims]?$/', $value, $match))
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
      $this->test->is(count($this->getResponseDomCssSelector()->getElements($selector)), $options['count'], sprintf('response selector "%s" matches "%s" times', $selector, $options['count']));
    }

    return $this;
  }
}
