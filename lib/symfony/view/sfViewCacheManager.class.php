<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class to cache the HTML results for Actions and Templates.
 *
 * This class is based on the PEAR_Cache_Liste class.
 * All cache files are stored in files in the SF_ROOT_DIR.'/cache/'.SF_APP.'/html' directory.
 * To disable all caching, you can set to false SF_CACHE constant.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCache.class.php 374 2005-08-20 08:57:25Z fabien $
 */
class sfViewCacheManager
{
  const
    CURRENT_URI         = 1;

  private
    $cache              = null,
    $config             = array(),
    $viewCacheClassName = '',
    $context            = null,
    $controller         = null;

  private static
    $current_suffix     = 0;

  public function initialize($context)
  {
    // cache only works with routing
    if (!SF_ROUTING)
    {
      $error = 'You must activate routing to use cache system';
      throw new sfConfigurationException($error);
    }

    $this->context    = $context;
    $this->controller = $context->getController();

    // empty configuration
    $this->config = array();

    // create cache instance
    $this->cache = new $this->viewCacheClassName(SF_TEMPLATE_CACHE_DIR);
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
    $uri = $this->controller->genUrl(null, $internalUri);

    // add hostname to uri
    $request = $this->context->getRequest();
    $hostName = $request->getHost();
    $hostName = preg_replace('/[^a-z0-9]/i', '_', $hostName);
    $hostName = preg_replace('/_+/', '_', $hostName);
    $uri = '/'.$hostName.'/'.$uri;

    // replace multiple /
    $uri = preg_replace('#/+#', '/', $uri);

    return $uri;
  }

  public function addCache($moduleName, $actionName, $suffix = 'slot', $lifeTime)
  {
    $entry = $moduleName.'_'.$actionName.'_'.$suffix;
    $this->config[$entry] = array(
      'lifeTime' => $lifeTime,
    );
  }

  public function getLifeTime($internalUri, $suffix = 'slot')
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);

    $entry = $params['module'].'_'.$params['action'].'_'.$suffix;

    $lifeTime = 0;
    if (isset($this->config[$entry]['lifeTime']))
    {
      $lifeTime = $this->config[$entry]['lifeTime'];
    }
    else if (isset($this->config[$params['module'].'_DEFAULT_'.$suffix]['lifeTime']))
    {
      $lifeTime = $this->config[$params['module'].'_DEFAULT_'.$suffix]['lifeTime'];
    }

    return $lifeTime;
  }

  private function hasCacheConfig($internalUri, $suffix)
  {
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $entry = $params['module'].'_'.$params['action'].'_'.$suffix;

    if (isset($this->config[$entry]) || isset($this->config[$params['module'].'_DEFAULT_'.$suffix]))
    {
      return true;
    }

    return false;
  }

  public function get($internalUri, $suffix = 'slot')
  {
    // no cache or no cache set for this action
    if (!SF_CACHE || !$this->hasCacheConfig($internalUri, $suffix))
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri);
    $id        = $suffix;

    $this->cache->setLifeTime($this->getLifeTime($internalUri, $suffix));

    $data = $this->cache->get($id, $namespace);

    if (SF_WEB_DEBUG && $data)
    {
      $data = sfWebDebug::getInstance()->decorateContentWithDebug($internalUri, $suffix, $data, '#f00', '#ff9');
    }

    return $data;
  }

  public function has($internalUri, $suffix = 'slot')
  {
    if (!SF_CACHE || !$this->hasCacheConfig($internalUri, $suffix))
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri);
    $id        = $suffix;

    $this->cache->setLifeTime($this->getLifeTime($internalUri, $suffix));

    return $this->cache->has($id, $namespace);
  }

  public function set($data, $internalUri, $suffix = 'slot')
  {
    if (!SF_CACHE || !$this->hasCacheConfig($internalUri, $suffix))
    {
      return $data;
    }

    $namespace = $this->generateNamespace($internalUri);
    $id        = $suffix;

    if (SF_LOGGING_ACTIVE)
    {
      $length = strlen($data);
    }

    $ret = $this->cache->set($id, $namespace, $data);
    if (SF_LOGGING_ACTIVE)
    {
      if (!$ret)
      {
        $this->context->getLogger()->err('{sfViewCacheManager} error writing cache for "'.$namespace.'" and id "'.$id.'"');
      }
      else
      {
        if (strlen($data) - $length)
        {
          $this->context->getLogger()->info('{sfViewCacheManager} save optimized content ('.sprintf('%d', strlen($data) - $length).' &raquo; '.sprintf('%.0f', ((strlen($data) - $length) / $length) * 100).'%)');
        }
        else
        {
          $this->context->getLogger()->info('{sfViewCacheManager} save content');
        }
      }
    }

    if (SF_WEB_DEBUG)
    {
      $data = sfWebDebug::getInstance()->decorateContentWithDebug($internalUri, $suffix, $data, '#f00', '#9ff');
    }

    return $data;
  }

  public function remove($internalUri, $suffix = null)
  {
    if (!SF_CACHE)
    {
      return null;
    }

    if ($suffix !== null)
    {
      $this->doRemove($internalUri, $suffix);
    }
    else
    {
      $namespace = $this->generateNamespace($internalUri);
      $this->clean($namespace);
    }
  }

  public function doRemove($internalUri, $suffix)
  {
    $namespace = $this->generateNamespace($internalUri);
    $id        = $suffix;

    $this->context->getLogger()->info('{sfViewCacheManager} remove cache for "'.$internalUri.'" / "'.$suffix.'"');

    $this->cache->remove($id, $namespace);
  }

  public function clean($namespace = null, $mode = 'all')
  {
    $this->cache->clean($namespace, $mode);
  }

  public function lastModified($internalUri, $suffix = 'slot')
  {
    if (!SF_CACHE || !$this->hasCacheConfig($internalUri, $suffix))
    {
      return null;
    }

    $namespace = $this->generateNamespace($internalUri);
    $id        = $suffix;

    return $this->cache->lastModified($id, $namespace);
  }

  /**
  * Start the cache
  *
  * @param  string  unique fragment name
  * @return boolean cache life time
  */
  public function start($suffix, $lifeTime)
  {
    $suffix = 'fragment_'.$suffix;

    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();

    // add cache config to cache manager
    list($route_name, $params) = $this->controller->convertUrlStringToParameters($internalUri);
    $this->addCache($params['module'], $params['action'], $suffix, $lifeTime);

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
    $suffix = 'fragment_'.$suffix;

    $data = ob_get_clean();

    // save content to cache
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();
    $data        = $this->set($data, $internalUri, $suffix);

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

?>