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

  public function generateNamespace($internalUri, $suffix)
  {
    // generate uri
    $uri = $this->controller->genUrl($internalUri);

    // prefix with vary headers
    $varyHeaders = $this->getVary($internalUri, $suffix);
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

    return $uri;
  }

  public function getInternalUri($suffix = 'slot')
  {
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();

    if ($suffix == 'page')
    {
      return array($internalUri, $suffix);
    }

    // our action is used in a slot context?
    $actionStackEntry = $this->getContext()->getController()->getActionStack()->getLastEntry();
    if ($actionStackEntry->isSlot())
    {
      $suffix = preg_replace('/[^a-z0-9]/i', '_', $internalUri);
      $suffix = preg_replace('/_+/', '_', $suffix);

      $actionInstance = $actionStackEntry->getActionInstance();
      $moduleName     = $actionInstance->getModuleName();
      $actionName     = $actionInstance->getActionName();
      $internalUri    = $moduleName.'/'.$actionName;

      // we add cache information based on slot configuration for this module/action
      $lifeTime = $this->getLifeTime($internalUri, 'slot');
      $this->addCache($moduleName, $actionName, $suffix, $lifeTime, $this->getClientLifeTime($internalUri, 'slot'), $this->getVary($internalUri, 'slot'));
    }

    return array($internalUri, $suffix);
  }

  public function addCache($moduleName, $actionName, $suffix = 'slot', $lifeTime, $clientLifeTime = null, $vary = array())
  {
    // normalize vary headers
    foreach ($vary as $key => $name)
    {
      $vary[$key] = strtr(strtolower($name), '_', '-');
    }

    $entry = $moduleName.'_'.$actionName.'_'.$suffix;
    $this->cacheConfig[$entry] = array(
      'lifeTime'       => $lifeTime,
      'clientLifeTime' => ($clientLifeTime !== null) ? $clientLifeTime : $lifeTime,
      'vary'           => $vary,
    );
  }

  public function getLifeTime($internalUri, $suffix = 'slot')
  {
    return $this->getCacheConfig($internalUri, $suffix, 'lifeTime', 0);
  }

  public function getClientLifeTime($internalUri, $suffix = 'slot')
  {
    return $this->getCacheConfig($internalUri, $suffix, 'clientLifeTime', 0);
  }

  public function getVary($internalUri, $suffix = 'slot')
  {
    return $this->getCacheConfig($internalUri, $suffix, 'vary', array());
  }

  private function getCacheConfig($internalUri, $suffix, $key, $defaultValue = null)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);

    $entry = $params['module'].'_'.$params['action'].'_'.$suffix;

    $value = $defaultValue;
    if (isset($this->cacheConfig[$entry][$key]))
    {
      $value = $this->cacheConfig[$entry][$key];
    }
    else if (isset($this->cacheConfig[$params['module'].'_DEFAULT_'.$suffix][$key]))
    {
      $value = $this->cacheConfig[$params['module'].'_DEFAULT_'.$suffix][$key];
    }

    return $value;
  }

  public function isCacheable($internalUri, $suffix)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $entry = $params['module'].'_'.$params['action'].'_'.$suffix;

    if (
      (isset($this->cacheConfig[$entry]) && $this->cacheConfig[$entry]['lifeTime'] > 0)
      ||
      (isset($this->cacheConfig[$params['module'].'_DEFAULT_'.$suffix]) && $this->cacheConfig[$params['module'].'_DEFAULT_'.$suffix]['lifeTime'] > 0)
    )
    {
      return true;
    }

    return false;
  }

  public function get($internalUri, $suffix = 'slot')
  {
    // no cache or no cache set for this action
    if (!sfConfig::get('sf_cache') || !$this->isCacheable($internalUri, $suffix) || $this->ignore())
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri, $suffix);
    $id        = $suffix;

    $this->cache->setLifeTime($this->getLifeTime($internalUri, $suffix));

    return $this->cache->get($id, $namespace);
  }

  public function has($internalUri, $suffix = 'slot')
  {
    if (!sfConfig::get('sf_cache') || !$this->isCacheable($internalUri, $suffix) || $this->ignore())
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri, $suffix);
    $id        = $suffix;

    $this->cache->setLifeTime($this->getLifeTime($internalUri, $suffix));

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

  public function set($data, $internalUri, $suffix = 'slot')
  {
    if (!sfConfig::get('sf_cache') || !$this->isCacheable($internalUri, $suffix))
    {
      return false;
    }

    $namespace = $this->generateNamespace($internalUri, $suffix);
    $id        = $suffix;

    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      $length = strlen($data);
    }

    $ret = $this->cache->set($id, $namespace, $data);
    if ($sf_logging_active)
    {
      if (!$ret)
      {
        if (sfConfig::get('sf_logging_active')) $this->context->getLogger()->err('{sfViewCacheManager} error writing cache for "'.$namespace.'" and id "'.$id.'"');
      }
      else
      {
        if (strlen($data) - $length)
        {
          if (sfConfig::get('sf_logging_active')) $this->context->getLogger()->info('{sfViewCacheManager} save optimized content ('.sprintf('%d', strlen($data) - $length).' &raquo; '.sprintf('%.0f', ((strlen($data) - $length) / $length) * 100).'%)');
        }
        else
        {
          if (sfConfig::get('sf_logging_active')) $this->context->getLogger()->info('{sfViewCacheManager} save content');
        }
      }
    }

    return true;
  }

  public function remove($internalUri, $suffix = null)
  {
    if (!sfConfig::get('sf_cache'))
    {
      return null;
    }

    if ($suffix !== null)
    {
      $this->doRemove($internalUri, $suffix);
    }
    else
    {
      $namespace = $this->generateNamespace($internalUri, $suffix);
      $this->clean($namespace);
    }
  }

  public function doRemove($internalUri, $suffix)
  {
    $namespace = $this->generateNamespace($internalUri, $suffix);
    $id        = $suffix;

    if (sfConfig::get('sf_logging_active')) $this->context->getLogger()->info('{sfViewCacheManager} remove cache for "'.$internalUri.'" / "'.$suffix.'"');

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

  public function lastModified($internalUri, $suffix = 'slot')
  {
    if (!sfConfig::get('sf_cache') || !$this->isCacheable($internalUri, $suffix))
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri, $suffix);
    $id        = $suffix;

    return $this->cache->lastModified($id, $namespace);
  }

  /**
  * Start the cache
  *
  * @param  string  unique fragment name
  * @return boolean cache life time
  */
  public function start($suffix, $lifeTime, $clientLifeTime = null, $vary = array())
  {
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();

    if (!$clientLifeTime)
    {
      $clientLifeTime = $lifeTime;
    }

    // add cache config to cache manager
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $this->addCache($params['module'], $params['action'], $suffix, $lifeTime, $clientLifeTime, $vary);

    // get data from cache if available
    $data = $this->get($internalUri, $suffix);
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
  public function stop($suffix)
  {
    $data = ob_get_clean();

    // save content to cache
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();
    $this->set($data, $internalUri, $suffix);

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
