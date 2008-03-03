<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class to cache the HTML results for actions and templates.
 *
 * This class uses a sfCache instance implementation to store cache.
 *
 * To disable all caching, you can set the [sf_cache] constant to false.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfViewCacheManager
{
  protected
    $cache       = null,
    $cacheConfig = array(),
    $context     = null,
    $dispatcher  = null,
    $controller  = null,
    $routing     = null,
    $loaded      = array();

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct($context, sfCache $cache)
  {
    $this->initialize($context, $cache);
  }

  /**
   * Initializes the cache manager.
   *
   * @param sfContext Current application context
   * @param sfCache   An sfCache instance
   */
  public function initialize($context, sfCache $cache)
  {
    $this->context    = $context;
    $this->dispatcher = $context->getEventDispatcher();
    $this->controller = $context->getController();

    // empty configuration
    $this->cacheConfig = array();

    // cache instance
    $this->cache = $cache;

    // register a named route for our partial cache (at the end)
    $this->routing = $context->getRouting();
    if (!$this->routing->hasRouteName('sf_cache_partial'))
    {
      $this->routing->connect('sf_cache_partial', '/sf_cache_partial/:module/:action/:sf_cache_key.', array(), array());
    }
  }

  /**
   * Retrieves the current cache context.
   *
   * @return sfContext The sfContext instance
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Retrieves the current cache object.
   *
   * @return sfCache The current cache object
   */
  public function getCache()
  {
    return $this->cache;
  }

  /**
   * Generates a unique cache key for an internal URI.
   *
   * @param  string The internal unified resource identifier.
   *
   * @return string The cache key
   */
  public function generateCacheKey($internalUri)
  {
    if ($callable = sfConfig::get('sf_cache_namespace_callable'))
    {
      if (!is_callable($callable))
      {
        throw new sfException(sprintf('"%s" cannot be called as a function.', var_export($callable, true)));
      }

      return call_user_func($callable, $internalUri);
    }

    // generate uri
    // we want our URL with / only
    $oldUrlFormat = sfConfig::get('sf_url_format');
    sfConfig::set('sf_url_format', 'PATH');
    if ($this->isContextual($internalUri))
    {
      list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
      $uri = $this->controller->genUrl($this->routing->getCurrentInternalUri()).sprintf('/%s/%s/%s', $params['module'], $params['action'], $params['sf_cache_key']);
    }
    else
    {
      $uri = $this->controller->genUrl($internalUri);
    }
    sfConfig::set('sf_url_format', $oldUrlFormat);

    // prefix with vary headers
    $varyHeaders = $this->getVary($internalUri);
    if ($varyHeaders)
    {
      sort($varyHeaders);
      $request = $this->context->getRequest();
      $vary = '';

      foreach ($varyHeaders as $header)
      {
        $vary .= $request->getHttpHeader($header).'|';
      }

      $vary = $vary;
    }
    else
    {
      $vary = 'all';
    }

    // prefix with hostname
    $request = $this->context->getRequest();
    $hostName = $request->getHost();
    $hostName = preg_replace('/[^a-z0-9]/i', '_', $hostName);
    $hostName = strtolower(preg_replace('/_+/', '_', $hostName));

    $uri = '/'.$hostName.'/'.$vary.'/'.$uri;

    // replace multiple /
    $uri = preg_replace('#/+#', '/', $uri);

    return $uri;
  }

  /**
   * Adds a cache to the manager.
   *
   * @param string Module name
   * @param string Action name
   * @param array Options for the cache
   */
  public function addCache($moduleName, $actionName, $options = array())
  {
    // normalize vary headers
    foreach ($options['vary'] as $key => $name)
    {
      $options['vary'][$key] = strtr(strtolower($name), '_', '-');
    }

    $options['lifeTime'] = isset($options['lifeTime']) ? $options['lifeTime'] : 0;
    if (!isset($this->cacheConfig[$moduleName]))
    {
      $this->cacheConfig[$moduleName] = array();
    }
    $this->cacheConfig[$moduleName][$actionName] = array(
      'withLayout'     => isset($options['withLayout']) ? $options['withLayout'] : false,
      'lifeTime'       => $options['lifeTime'],
      'clientLifeTime' => isset($options['clientLifeTime']) && $options['clientLifeTime'] ? $options['clientLifeTime'] : $options['lifeTime'],
      'contextual'     => isset($options['contextual']) ? $options['contextual'] : false,
      'vary'           => isset($options['vary']) ? $options['vary'] : array(),
    );
  }

  /**
   * Registers configuration options for the cache.
   *
   * @param string Module name
   */
  public function registerConfiguration($moduleName)
  {
    if (!isset($this->loaded[$moduleName]))
    {
      require($this->context->getConfigCache()->checkConfig('modules/'.$moduleName.'/config/cache.yml'));
      $this->loaded[$moduleName] = true;
    }
  }

  /**
   * Retrieves the layout from the cache option list.
   *
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if have layout otherwise false
   */
  public function withLayout($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'withLayout', false);
  }

  /**
   * Retrieves lifetime from the cache option list.
   *
   * @param string Internal uniform resource identifier
   *
   * @return int LifeTime
   */
  public function getLifeTime($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'lifeTime', 0);
  }

  /**
   * Retrieves client lifetime from the cache option list
   *
   * @param string Internal uniform resource identifier
   *
   * @return int Client lifetime
   */
  public function getClientLifeTime($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'clientLifeTime', 0);
  }

  /**
   * Retrieves contextual option from the cache option list.
   *
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if is contextual otherwise false
   */
  public function isContextual($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'contextual', false);
  }

  /**
   * Retrieves vary option from the cache option list.
   *
   * @param string Internal uniform resource identifier
   *
   * @return array Vary options for the cache
   */
  public function getVary($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'vary', array());
  }

  /**
   * Gets a config option from the cache.
   *
   * @param string Internal uniform resource identifier
   * @param string Option name
   * @param string Default value of the option
   *
   * @return mixed Value of the option
   */
  protected function getCacheConfig($internalUri, $key, $defaultValue = null)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);

    $value = $defaultValue;
    if (isset($this->cacheConfig[$params['module']][$params['action']][$key]))
    {
      $value = $this->cacheConfig[$params['module']][$params['action']][$key];
    }
    else if (isset($this->cacheConfig[$params['module']]['DEFAULT'][$key]))
    {
      $value = $this->cacheConfig[$params['module']]['DEFAULT'][$key];
    }

    return $value;
  }

  /**
   * Returns true if the current content is cacheable.
   *
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if the content is cacheable otherwise false
   */
  public function isCacheable($internalUri)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);

    if (isset($this->cacheConfig[$params['module']][$params['action']]))
    {
      return ($this->cacheConfig[$params['module']][$params['action']]['lifeTime'] > 0);
    }
    else if (isset($this->cacheConfig[$params['module']]['DEFAULT']))
    {
      return ($this->cacheConfig[$params['module']]['DEFAULT']['lifeTime'] > 0);
    }

    return false;
  }

  /**
   * Retrieves content in the cache.
   *
   * @param  string Internal uniform resource identifier
   *
   * @return string The content in the cache
   */
  public function get($internalUri)
  {
    // no cache or no cache set for this action
    if (!$this->isCacheable($internalUri) || $this->ignore())
    {
      return null;
    }

    $retval = $this->cache->get($this->generateCacheKey($internalUri));

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Cache for "%s" %s', $internalUri, $retval !== null ? 'exists' : 'does not exist'))));
    }

    return $retval;
  }

  /**
   * Returns true if there is a cache.
   *
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if there is a cache otherwise false
   */
  public function has($internalUri)
  {
    if (!$this->isCacheable($internalUri) || $this->ignore())
    {
      return null;
    }

    return $this->cache->has($this->generateCacheKey($internalUri));
  }

  /**
   * Ignores the cache functionality.
   *
   * @return boolean true, if the cache is ignore otherwise false
   */
  protected function ignore()
  {
    // ignore cache parameter? (only available in debug mode)
    if (sfConfig::get('sf_debug') && $this->context->getRequest()->getAttribute('_sf_ignore_cache'))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array('Discard cache')));
      }

      return true;
    }

    return false;
  }

  /**
   * Sets the cache content.
   *
   * @param string Data to put in the cache
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if the data get set successfully otherwise false
   */
  public function set($data, $internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return false;
    }

    try
    {
      $ret = $this->cache->set($this->generateCacheKey($internalUri), $data, $this->getLifeTime($internalUri));
    }
    catch (Exception $e)
    {
      return false;
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Save cache for "%s"', $internalUri))));
    }

    return true;
  }

  /**
   * Removes the content in the cache.
   *
   * @param string Internal uniform resource identifier
   *
   * @return boolean true, if the remove happend otherwise false
   */
  public function remove($internalUri)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Remove cache for "%s"', $internalUri))));
    }

    $key = $this->generateCacheKey($internalUri);
    if ($this->cache->has($key))
    {
      $this->cache->remove($key);
    }
  }

  /**
   * Retrieves the last modified time.
   *
   * @param  string Internal uniform resource identifier
   *
   * @return int    The last modified datetime
   */
  public function getLastModified($internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return 0;
    }

    return $this->cache->getLastModified($this->generateCacheKey($internalUri));
  }

  /**
   * Retrieves the timeout.
   *
   * @param  string Internal uniform resource identifier
   *
   * @return int    The timeout datetime
   */
  public function getTimeout($internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return 0;
    }

    return $this->cache->getTimeout($this->generateCacheKey($internalUri));
  }

  /**
   * Starts the fragment cache.
   *
   * @param string Unique fragment name
   * @param string Life time for the cache
   * @param string Client life time for the cache
   * @param array Vary options for the cache
   *
   * @return boolean true, if success otherwise false
   */
  public function start($name, $lifeTime, $clientLifeTime = null, $vary = array())
  {
    $internalUri = $this->routing->getCurrentInternalUri();

    if (!$clientLifeTime)
    {
      $clientLifeTime = $lifeTime;
    }

    // add cache config to cache manager
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $this->addCache($params['module'], $params['action'], array('withLayout' => false, 'lifeTime' => $lifeTime, 'clientLifeTime' => $clientLifeTime, 'vary' => $vary));

    // get data from cache if available
    $data = $this->get($internalUri.(strpos($internalUri, '?') ? '&' : '?').'_sf_cache_key='.$name);
    if ($data !== null)
    {
      return $data;
    }
    else
    {
      ob_start();
      ob_implicit_flush(0);

      return null;
    }
  }

  /**
   * Stops the fragment cache.
   *
   * @param string Unique fragment name
   *
   * @return boolean true, if success otherwise false
   */
  public function stop($name)
  {
    $data = ob_get_clean();

    // save content to cache
    $internalUri = $this->routing->getCurrentInternalUri();
    try
    {
      $this->set($data, $internalUri.(strpos($internalUri, '?') ? '&' : '?').'_sf_cache_key='.$name);
    }
    catch (Exception $e)
    {
    }

    return $data;
  }

  /**
   * Computes the cache key based on the passed parameters.
   *
   * @param array An array of parameters
   */
  public function computeCacheKey(array $parameters)
  {
    return isset($parameters['sf_cache_key']) ? $parameters['sf_cache_key'] : md5(serialize($parameters));
  }

  /**
   * Computes a partial internal URI.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The cache key
   *
   * @return string The internal URI
   */
  public function getPartialUri($module, $action, $cacheKey)
  {
    return sprintf('@sf_cache_partial?module=%s&action=%s&sf_cache_key=%s', $module, $action, $cacheKey);
  }

  /**
   * Returns whether a partial template is in the cache.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The cache key
   *
   * @return Boolean true if a partial is in the cache, false otherwise
   */
  public function hasPartialCache($module, $action, $cacheKey)
  {
    return $this->has($this->getPartialUri($module, $action, $cacheKey));
  }

  /**
   * Gets a partial template from the cache.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The cache key
   *
   * @return string The cache content
   */
  public function getPartialCache($module, $action, $cacheKey)
  {
    $uri = $this->getPartialUri($module, $action, $cacheKey);

    if (!$this->isCacheable($uri))
    {
      return null;
    }

    // retrieve content from cache
    $cache = $this->get($uri);

    if (is_null($cache))
    {
      return null;
    }

    $cache = unserialize($cache);
    $content = $cache['content'];
    $this->context->getResponse()->merge($cache['response']);

    if (sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::decorateContentWithDebug($uri, $content, true);
    }

    return $content;
  }

  /**
   * Sets an action template in the cache.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The cache key
   * @param  string The content to cache
   *
   * @return string The cached content
   */
  public function setPartialCache($module, $action, $cacheKey, $content)
  {
    $uri = $this->getPartialUri($module, $action, $cacheKey);
    if (!$this->isCacheable($uri))
    {
      return $content;
    }

    $saved = $this->set(serialize(array('content' => $content, 'response' => $this->context->getResponse())), $uri);

    if ($saved && sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::decorateContentWithDebug($uri, $content, true);
    }

    return $content;
  }

  /**
   * Returns whether an action template is in the cache.
   *
   * @param  string  The internal URI
   *
   * @return Boolean true if an action is in the cache, false otherwise
   */
  public function hasActionCache($uri)
  {
    return $this->has($uri) && !$this->withLayout($uri);
  }

  /**
   * Gets an action template from the cache.
   *
   * @param  string The internal URI
   *
   * @return array  An array composed of the cached content and the view attribute holder
   */
  public function getActionCache($uri)
  {
    if (!$this->isCacheable($uri) || $this->withLayout($uri))
    {
      return null;
    }

    // retrieve content from cache
    $cache = $this->get($uri);

    if (is_null($cache))
    {
      return null;
    }

    $cache = unserialize($cache);
    $content = $cache['content'];
    $cache['response']->setEventDispatcher($this->dispatcher);
    $this->context->getResponse()->copyProperties($cache['response']);

    if (sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::decorateContentWithDebug($uri, $content, false);
    }

    return array($content, $cache['attributeHolder'], $cache['decoratorTemplate']);
  }

  /**
   * Sets an action template in the cache.
   *
   * @param  string The internal URI
   * @param  string The content to cache
   * @param  string The view attribute holder to cache
   *
   * @return string The cached content
   */
  public function setActionCache($uri, $content, $attributeHolder, $decoratorTemplate)
  {
    if (!$this->isCacheable($uri) || $this->withLayout($uri))
    {
      return $content;
    }

    $saved = $this->set(serialize(array('content' => $content, 'attributeHolder' => $attributeHolder, 'decoratorTemplate' => $decoratorTemplate, 'response' => $this->context->getResponse())), $uri);

    if ($saved && sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::decorateContentWithDebug($uri, $content, true);
    }

    return $content;
  }

  /**
   * Sets a page in the cache.
   *
   * @param string The internal URI
   */
  public function setPageCache($uri)
  {
    if (sfView::RENDER_CLIENT != $this->controller->getRenderMode())
    {
      return;
    }

    // save content in cache
    $saved = $this->set(serialize($this->context->getResponse()), $uri);

    if ($saved && sfConfig::get('sf_web_debug'))
    {
      $content = sfWebDebug::decorateContentWithDebug($uri, $this->context->getResponse()->getContent(), true);
      $this->context->getResponse()->setContent($content);
    }
  }

  /**
   * Gets a page from the cache.
   *
   * @param  string The internal URI
   *
   * @return string The cached page
   */
  public function getPageCache($uri)
  {
    $retval = $this->get($uri);

    if (is_null($retval))
    {
      return false;
    }

    $cachedResponse = unserialize($retval);
    $cachedResponse->setEventDispatcher($this->dispatcher);

    if (sfView::RENDER_VAR == $this->controller->getRenderMode())
    {
      $this->controller->getActionStack()->getLastEntry()->setPresentation($cachedResponse->getContent());
      $this->response->setContent('');
    }
    else
    {
      $this->context->setResponse($cachedResponse);

      if (sfConfig::get('sf_web_debug'))
      {
        $content = sfWebDebug::decorateContentWithDebug($uri, $this->context->getResponse()->getContent(), false);
        $this->context->getResponse()->setContent($content);
      }
    }

    return true;
  }
}
