<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCacheFilter deals with page caching and action caching.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCacheFilter extends sfFilter
{
  protected
    $cacheManager = null,
    $request      = null,
    $response     = null,
    $routing      = null,
    $cache        = array();

  /**
   * Initializes this Filter.
   *
   * @param sfContext $context      The current application context
   * @param array     $parameters   An associative array of initialization parameters
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Filter
   */
  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->cacheManager = $context->getViewCacheManager();
    $this->request      = $context->getRequest();
    $this->response     = $context->getResponse();
    $this->routing      = $context->getRouting();
  }

  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute this filter only once, if cache is set and no GET or POST parameters
    if (!sfConfig::get('sf_cache'))
    {
      $filterChain->execute();

      return;
    }

    if ($this->executeBeforeExecution())
    {
      $filterChain->execute();
    }

    $this->executeBeforeRendering();
  }

  public function executeBeforeExecution()
  {
    // register our cache configuration
    $this->cacheManager->registerConfiguration($this->context->getModuleName());

    $uri = $this->routing->getCurrentInternalUri();

    if (is_null($uri))
    {
      return true;
    }

    // page cache
    $cacheable = $this->cacheManager->isCacheable($uri);
    if ($cacheable && $this->cacheManager->withLayout($uri))
    {
      $inCache = $this->cacheManager->getPageCache($uri);
      $this->cache[$uri] = $inCache;

      if ($inCache)
      {
        // page is in cache, so no need to run execution filter
        return false;
      }
    }

    return true;
  }

  /**
   * Executes this filter.
   */
  public function executeBeforeRendering()
  {
    // cache only 200 HTTP status
    if (200 != $this->response->getStatusCode())
    {
      return;
    }

    $uri = $this->routing->getCurrentInternalUri();

    // save page in cache
    if (isset($this->cache[$uri]) && false === $this->cache[$uri])
    {
      // set some headers that deals with cache
      if ($lifetime = $this->cacheManager->getClientLifeTime($uri, 'page'))
      {
        $this->response->setHttpHeader('Last-Modified', $this->response->getDate(time()), false);
        $this->response->setHttpHeader('Expires', $this->response->getDate(time() + $lifetime), false);
        $this->response->addCacheControlHttpHeader('max-age', $lifetime);
      }

      // set Vary headers
      foreach ($this->cacheManager->getVary($uri, 'page') as $vary)
      {
        $this->response->addVaryHttpHeader($vary);
      }

      $this->cacheManager->setPageCache($uri);
    }

    // remove PHP automatic Cache-Control and Expires headers if not overwritten by application or cache
    if ($this->response->hasHttpHeader('Last-Modified') || sfConfig::get('sf_etag'))
    {
      // FIXME: these headers are set by PHP sessions (see session_cache_limiter())
      $this->response->setHttpHeader('Cache-Control', null, false);
      $this->response->setHttpHeader('Expires', null, false);
      $this->response->setHttpHeader('Pragma', null, false);
    }

    // Etag support
    if (sfConfig::get('sf_etag'))
    {
      $etag = '"'.md5($this->response->getContent()).'"';
      $this->response->setHttpHeader('ETag', $etag);

      if ($this->request->getHttpHeader('IF_NONE_MATCH') == $etag)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);

        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('ETag matches If-None-Match (send 304)')));
        }
      }
    }

    // conditional GET support
    // never in debug mode
    if ($this->response->hasHttpHeader('Last-Modified') && !sfConfig::get('sf_debug'))
    {
      $last_modified = $this->response->getHttpHeader('Last-Modified');
      $last_modified = $last_modified[0];
      if ($this->request->getHttpHeader('IF_MODIFIED_SINCE') == $last_modified)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);

        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('Last-Modified matches If-Modified-Since (send 304)')));
        }
      }
    }
  }
}
