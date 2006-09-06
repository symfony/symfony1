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
 * This class users viewCacheClassName to serialize cache.
 * All cache files are stored in files in the [sf_root_dir].'/cache/'.[sf_app].'/html' directory.
 * To disable all caching, you can set to false [sf_cache] constant.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfViewCacheManager
{
  private
    $cache              = null,
    $cacheConfig        = array(),
    $viewCacheClassName = '',
    $context            = null,
    $controller         = null;

  public function initialize($context)
  {
    $this->context = $context;

    $this->controller = $context->getController();

    // empty configuration
    $this->cacheConfig = array();

    // create cache instance
    $this->cache = new $this->viewCacheClassName(sfConfig::get('sf_template_cache_dir'));
  }

  public function getContext()
  {
    return $this->context;
  }

  /**
   * Set the name of the sfCache class to use
   *
   * @param string The class name of the sfCache to use
   *
   * @return void
   */
  public function setViewCacheClassName($className)
  {
    $this->viewCacheClassName = $className;
  }

  public function generateNamespace($internalUri)
  {
    // generate uri
    $uri = $this->controller->genUrl($internalUri);

    // prefix with vary headers
    $varyHeaders = $this->getVary($internalUri);
    if ($varyHeaders)
    {
      sort($varyHeaders);
      $request = $this->getContext()->getRequest();
      $vary = '';

      foreach ($varyHeaders as $header)
      {
        $vary .= $request->getHttpHeader($header).'|';
      }

      $vary = md5($vary);
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

    // index
    if ('/' == $uri[strlen($uri) - 1])
    {
      $uri .= 'index';
    }

    return array(dirname($uri), basename($uri));
  }

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
      'vary'           => isset($options['vary']) ? $options['vary'] : array(),
    );
  }

  public function registerConfiguration($moduleName)
  {
    static $loaded = array();

    if (!isset($loaded[$moduleName]))
    {
      require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/cache.yml'));
      $loaded[$moduleName] = true;
    }
  }

  public function withLayout($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'withLayout', false);
  }

  public function getLifeTime($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'lifeTime', 0);
  }

  public function getClientLifeTime($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'clientLifeTime', 0);
  }

  public function getVary($internalUri)
  {
    return $this->getCacheConfig($internalUri, 'vary', array());
  }

  private function getCacheConfig($internalUri, $key, $defaultValue = null)
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

  public function isCacheable($internalUri)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);

    if (
      (isset($this->cacheConfig[$params['module']][$params['action']]) && $this->cacheConfig[$params['module']][$params['action']]['lifeTime'] > 0)
      ||
      (isset($this->cacheConfig[$params['module']]['DEFAULT']) && $this->cacheConfig[$params['module']]['DEFAULT']['lifeTime'] > 0)
    )
    {
      return true;
    }

    return false;
  }

  public function get($internalUri)
  {
    // no cache or no cache set for this action
    if (!$this->isCacheable($internalUri) || $this->ignore())
    {
      return null;
    }

    list($namespace, $id) = $this->generateNamespace($internalUri);

    $this->cache->setLifeTime($this->getLifeTime($internalUri));

    $retval = $this->cache->get($id, $namespace);

    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info(sprintf('{sfViewCacheManager} cache for "%s" %s', $internalUri, ($retval !== null ? 'exists' : 'does not exist')));
    }

    return $retval;
  }

  public function has($internalUri)
  {
    if (!$this->isCacheable($internalUri) || $this->ignore())
    {
      return null;
    }

    list($namespace, $id) = $this->generateNamespace($internalUri);

    $this->cache->setLifeTime($this->getLifeTime($internalUri));

    return $this->cache->has($id, $namespace);
  }

  protected function ignore()
  {
    // ignore cache parameter? (only available in debug mode)
    if (sfConfig::get('sf_debug') && $this->getContext()->getRequest()->getParameter('_sf_ignore_cache', false, 'symfony/request/sfWebRequest') == true)
    {
      if (sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfViewCacheManager} discard cache');
      }

      return true;
    }

    return false;
  }

  public function set($data, $internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return false;
    }

    list($namespace, $id) = $this->generateNamespace($internalUri);

    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      $length = strlen($data);
    }

    $ret = $this->cache->set($id, $namespace, $data);
    if ($sf_logging_active)
    {
      if (!$ret)
      {
        if (sfConfig::get('sf_logging_active'))
        {
          $this->context->getLogger()->err(sprintf('{sfViewCacheManager} error saving cache for "%s"', $internalUri));
        }
      }
      else
      {
        if (strlen($data) - $length)
        {
          if (sfConfig::get('sf_logging_active'))
          {
            $this->context->getLogger()->info(sprintf('{sfViewCacheManager} save optimized cache for "%s" (%d% &raquo; %.0f%%)', $internalUri, strlen($data) - $length), (strlen($data) - $length) * 100 / $length);
          }
        }
        else
        {
          if (sfConfig::get('sf_logging_active'))
          {
            $this->context->getLogger()->info(sprintf('{sfViewCacheManager} save cache for "%s"', $internalUri));
          }
        }
      }
    }

    return true;
  }

  public function remove($internalUri)
  {
    list($namespace, $id) = $this->generateNamespace($internalUri);

    if (sfConfig::get('sf_logging_active'))
    {
      $this->context->getLogger()->info(sprintf('{sfViewCacheManager} remove cache for "%s"', $internalUri));
    }

    if ($this->cache->has($id, $namespace))
    {
      $this->cache->remove($id, $namespace);
    }
  }

  public function clean($namespace = null, $mode = 'all')
  {
    try
    {
      $this->cache->clean($namespace, $mode);
    }
    catch (sfCacheException $e) {}
  }

  public function lastModified($internalUri)
  {
    if (!$this->isCacheable($internalUri))
    {
      return null;
    }

    list($namespace, $id) = $this->generateNamespace($internalUri);

    return $this->cache->lastModified($id, $namespace);
  }

  /**
  * Start the cache
  *
  * @param  string  unique fragment name
  * @return boolean cache life time
  */
  public function start($name, $lifeTime, $clientLifeTime = null, $vary = array())
  {
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();

    if (!$clientLifeTime)
    {
      $clientLifeTime = $lifeTime;
    }

    // add cache config to cache manager
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $this->addCache($params['module'], $params['action'], array('withLayout' => false, 'lifeTime' => $lifeTime, 'clientLifeTime' => $clientLifeTime, 'vary' => $vary));

    // get data from cache if available
    $data = $this->get($internalUri, $name);
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
  * Stop the cache
  */
  public function stop($name)
  {
    $data = ob_get_clean();

    // save content to cache
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();
    $this->set($data, $internalUri, $name);

    return $data;
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown ()
  {
  }
}
