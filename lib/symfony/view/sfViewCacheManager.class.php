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
    $context            = null;

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

    $this->context = $context;

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

  public function generateUri($moduleName, $actionName, $suffix, $parameters = array())
  {
    $controller = $this->context->getController();
    $request = $this->context->getRequest();

    if (is_array($parameters))
    {
      $parameters = array_merge(
        $parameters,
        array('module' => $moduleName, 'action' => $actionName)
      );
    }

    // generate uri
    $uri = $controller->genUrl(null, $parameters);

    // add hostname to uri
    $hostName = $request->getHost();
    $hostName = preg_replace('/[^a-z0-9]/i', '_', $hostName);
    $hostName = preg_replace('/_+/', '_', $hostName);
    $uri = '/'.$hostName.'/'.$uri;

    // remove suffix (.html)
    $uri = preg_replace('/'.SF_SUFFIX.'$/', '', $uri);

    // add suffix (slot, fragment, ...)
    if ($suffix)
    {
      $uri .= '/'.$suffix;
    }

    // replace multiple /
    $uri = preg_replace('#/+#', '/', $uri);

    return $uri;
  }

  public function getCurrentUri($suffix = '')
  {
    $request = $this->context->getRequest();

    // get the current action information
    $moduleName = $this->context->getModuleName();
    $actionName = $this->context->getActionName();

    $uri = $this->generateUri($moduleName, $actionName, $suffix, $request->getParameterHolder()->getAll());

    return $uri;
  }

  public function addCache($moduleName, $actionName, $suffix = 'slot', $lifeTime, $uri = sfViewCacheManager::CURRENT_URI)
  {
    $entry = $moduleName.'_'.$actionName.'_'.$suffix;

    if ($uri == sfViewCacheManager::CURRENT_URI)
    {
      $uri = $this->getCurrentUri($suffix);
    }
    else
    {
      $uri = $this->generateUri($moduleName, $actionName, $suffix, $uri);
    }

    $this->config[$entry] = array(
      'lifeTime' => $lifeTime,
      'uri' => $uri,
    );
  }

  public function getLifeTime($moduleName, $actionName, $suffix = 'slot')
  {
    $entry = $moduleName.'_'.$actionName.'_'.$suffix;

    $lifeTime = 0;
    if (isset($this->config[$entry]['lifeTime']))
    {
      $lifeTime = $this->config[$entry]['lifeTime'];
    }
    else if (isset($this->config[$moduleName.'_DEFAULT_'.$suffix]['lifeTime']))
    {
      $lifeTime = $this->config[$moduleName.'_DEFAULT_'.$suffix]['lifeTime'];
    }

    return $lifeTime;
  }

  public function getUri($moduleName, $actionName, $suffix = 'slot')
  {
    $entry = $moduleName.'_'.$actionName.'_'.$suffix;

    $uri = null;
    if (isset($this->config[$entry]['uri']))
    {
      $uri = $this->config[$entry]['uri'];
    }
    else if (isset($this->config[$moduleName.'_DEFAULT_'.$suffix]['uri']))
    {
      $uri = $this->config[$moduleName.'_DEFAULT_'.$suffix]['uri'];
    }

    return $uri;
  }

  private function splitUri($uri = sfViewCacheManager::CURRENT_URI)
  {
    if ($uri === null)
    {
      return array(null, null);
    }

    $request = $this->context->getRequest();

    // no uri, we get current one
    if ($uri == sfViewCacheManager::CURRENT_URI)
    {
      $uri = $this->getCurrentUri();
    }

    $id = basename($uri);
    $namespace = dirname($uri);

    return array($id, $namespace);
  }

  public function get($moduleName, $actionName, $suffix = 'slot')
  {
    if (!SF_CACHE)
    {
      return null;
    }

    list($id, $namespace) = $this->splitUri($this->getUri($moduleName, $actionName, $suffix));

    // no cache set for this action
    if ($id === null)
    {
      return null;
    }

    $this->cache->setLifeTime($this->getLifeTime($moduleName, $actionName, $suffix));

    $data = $this->cache->get($id, $namespace);

    if (SF_WEB_DEBUG && $data)
    {
      $data = sfWebDebug::decorateContentWithDebug($moduleName, $actionName, $suffix, $data, '#f00', '#ff9');
    }

    return $data;
  }

  public function has($moduleName, $actionName, $suffix = 'slot')
  {
    if (!SF_CACHE)
    {
      return null;
    }

    list($id, $namespace) = $this->splitUri($this->getUri($moduleName, $actionName, $suffix));

    // no cache set for this action
    if ($id === null)
    {
      return null;
    }

    $this->cache->setLifeTime($this->getLifeTime($moduleName, $actionName, $suffix));

    return $this->cache->has($id, $namespace);
  }

  public function set($data, $moduleName, $actionName, $suffix = 'slot')
  {
    list($id, $namespace) = $this->splitUri($this->getUri($moduleName, $actionName, $suffix));

    // no cache set for this action
    if ($id === null)
    {
      return $data;
    }

    if (SF_LOGGING_ACTIVE)
    {
      $length = strlen($data);
    }

    $ret = $this->cache->set($id, $namespace, $data);
    if (SF_LOGGING_ACTIVE)
    {
      if ($ret)
      {
        $this->getContext()->getLogger()->info('{sfViewCacheManager} error writing cache for "'.$namespace.'" and id "'.$id.'"');
      }
      else
      {
        if (strlen($data) - $length)
        {
          $this->getContext()->getLogger()->info('{sfViewCacheManager} save optimized content ('.sprintf('%d', strlen($data) - $length).' &raquo; '.sprintf('%.0f', ((strlen($data) - $length) / $length) * 100).'%)');
        }
        else
        {
          $this->getContext()->getLogger()->info('{sfViewCacheManager} save content');
        }
      }
    }

    if (SF_WEB_DEBUG)
    {
      $data = sfWebDebug::decorateContentWithDebug($moduleName, $actionName, $suffix, $data, '#f00', '#9ff');
    }

    return $data;
  }

  public function remove($moduleName, $actionName, $suffix = 'slot')
  {
    list($id, $namespace) = $this->splitUri($this->getUri($moduleName, $actionName, $suffix));

    // no cache set for this action
    if ($id === null)
    {
      return null;
    }

    $this->cache->setLifeTime($this->getLifeTime($moduleName, $actionName, $suffix));
    $this->cache->remove($id, $namespace);
  }

  public function clean($namespace = null, $mode = 'all')
  {
    $this->cache->clean($namespace, $mode);
  }

  public function lastModified($moduleName, $actionName, $suffix = 'slot')
  {
    list($id, $namespace) = $this->splitUri($this->getUri($moduleName, $actionName, $suffix));

    // no cache set for this action
    if ($id === null)
    {
      return null;
    }

    return $this->cache->lastModified($id, $namespace);
  }

  private function getCurrentSuffix()
  {
    return 'fragment'.self::$current_suffix;
  }

  /**
  * Start the cache
  *
  * @param  string  $id cache id
  * @param  string  $group name of the cache group
  * @return boolean true if the cache is hit (false else)
  */
  public function start($moduleName, $actionName, $lifeTime, $uri = sfViewCacheManager::CURRENT_URI)
  {
    // automatic fragment name
    ++self::$current_suffix;

    $suffix = $this->getCurrentSuffix();

    // add cache config to cache manager
    $this->addCache($moduleName, $actionName, $suffix, $lifeTime, $uri);

    // get data from cache if available
    $data = $this->get($moduleName, $actionName, $suffix);
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
  public function stop($moduleName, $actionName)
  {
    $data = ob_get_clean();

    // save content to cache
    $data = $this->set($data, $moduleName, $actionName, $this->getCurrentSuffix());

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