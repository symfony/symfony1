<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCacheFilter extends sfFilter
{
  private
    $cacheManager = null,
    $request      = null,
    $response     = null,
    $cache       = array();

  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->cacheManager = $context->getViewCacheManager();
    $this->request      = $context->getRequest();
    $this->response     = $context->getResponse();
  }

  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute ($filterChain)
  {
    // no cache if GET or POST parameters
    if (!sfConfig::get('sf_cache') || count($_GET) || count($_POST))
    {
      $filterChain->execute();

      return;
    }

    // register our cache configuration
    $this->cacheManager->registerConfiguration($this->getContext()->getModuleName());

    $uri = sfRouting::getInstance()->getCurrentInternalUri();

    // page cache
    $this->cache[$uri] = array('page' => false, 'action' => false);
    $cacheable = $this->cacheManager->isCacheable($uri);
    if ($cacheable)
    {
      if ($this->cacheManager->withLayout($uri))
      {
        $inCache = $this->getPageCache($uri);
        $this->cache[$uri]['page'] = !$inCache;

        if ($inCache)
        {
          // page is in cache, so no need to run execution filter
          $filterChain->executionFilterDone();
        }
      }
      else
      {
        $inCache = $this->getActionCache($uri);
        $this->cache[$uri]['action'] = !$inCache;
      }
    }

    $filterChain->execute();
  }

  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function executeBeforeRendering ($filterChain)
  {
    if (sfConfig::get('sf_cache'))
    {
      // no cache if GET or POST parameters
      if (count($_GET) || count($_POST))
      {
        $filterChain->execute();

        return;
      }

      // cache only 200 HTTP status
      if ($this->response->getStatusCode() == 200)
      {
        $uri = sfRouting::getInstance()->getCurrentInternalUri();

        // save page in cache
        if ($this->cache[$uri]['page'])
        {
          // set some headers that deals with cache
          $lifetime = $this->cacheManager->getClientLifeTime($uri, 'page');
          $this->response->setHttpHeader('Last-Modified', $this->response->getDate(time()), false);
          $this->response->setHttpHeader('Expires', $this->response->getDate(time() + $lifetime), false);
          $this->response->addCacheControlHttpHeader('max-age', $lifetime);

          // set Vary headers
          foreach ($this->cacheManager->getVary($uri, 'page') as $vary)
          {
            $this->response->addVaryHttpHeader($vary);
          }

          $this->setPageCache($uri);
        }
        else if ($this->cache[$uri]['action'])
        {
          // save action in cache
          $this->setActionCache($uri);
        }
      }
    }

    // execute next filter
    $filterChain->execute();
  }

  private function setPageCache($uri)
  {
    if ($this->getContext()->getController()->getRenderMode() != sfView::RENDER_CLIENT)
    {
      return;
    }

    // save content in cache
    $this->cacheManager->set(serialize($this->response), $uri, '.page');

    if (sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::getInstance()->decorateContentWithDebug($uri, 'page', $this->response->getContent(), true);
      $this->response->setContent($content);
    }
  }

  private function getPageCache($uri)
  {
    $context = $this->getContext();

    // get the current action information
    $moduleName = $context->getModuleName();
    $actionName = $context->getActionName();

    $retval = $this->cacheManager->get($uri, '.page');

    if ($retval === null)
    {
      return false;
    }

    $cachedResponse = unserialize($retval);
    $cachedResponse->setContext($context);

    $controller = $context->getController();
    if ($controller->getRenderMode() == sfView::RENDER_VAR)
    {
      $controller->getActionStack()->getLastEntry()->setPresentation($cachedResponse->getContent());
      $this->response->setContent('');
    }
    else
    {
      $context->setResponse($cachedResponse);

      if (sfConfig::get('sf_web_debug'))
      {
        $content = sfWebDebug::getInstance()->decorateContentWithDebug($uri, 'page', $cachedResponse->getContent(), false);
        $context->getResponse()->setContent($content);
      }
    }

    return true;
  }

  private function setActionCache($uri)
  {
    $content = $this->response->getParameter($uri.'_action', null, 'symfony/cache');

    if ($content !== null)
    {
      $cached = $this->cacheManager->set($content, $uri);
    }
  }

  private function getActionCache($uri)
  {
    // retrieve content from cache
    $retval = $this->cacheManager->get($uri);

    if ($retval && sfConfig::get('sf_web_debug'))
    {
      $tmp = unserialize($retval);
      $tmp['content'] = sfWebDebug::getInstance()->decorateContentWithDebug($uri, 'action', $tmp['content'], false);
      $retval = serialize($tmp);
    }

    $this->response->setParameter('current_key', $uri.'_action', 'symfony/cache/current');
    $this->response->setParameter($uri.'_action', $retval, 'symfony/cache');

    return ($retval ? true : false);
  }
}
