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
class sfTestFunctional extends sfTestFunctionalBase
{
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
    $actionStack = $this->browser->getContext()->getActionStack();

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
   * Tests if the given uri is cached.
   *
   * @param  boolean $boolean      Flag for checking the cache
   * @param  boolean $with_layout  If have or not layout
   *
   * @return sfTestBrowser The current sfTestBrowser instance
   */
  public function isCached($boolean, $with_layout = false)
  {
    return $this->isUriCached($this->browser->getContext()->getRouting()->getCurrentInternalUri(), $boolean, $with_layout);
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
    $cacheManager = $this->browser->getContext()->getViewCacheManager();

    // check that cache is enabled
    if (!$cacheManager)
    {
      $this->test()->ok(!$boolean, 'cache is disabled');

      return $this;
    }

    if ($uri == $this->browser->getContext()->getRouting()->getCurrentInternalUri())
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
