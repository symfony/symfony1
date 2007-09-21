<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in memcache.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMemcacheCache extends sfCache
{
  protected
    $prefix   = '',
    $memcache = null;

  /**
   * Initializes this sfCache instance.
   *
   * Available parameters:
   *
   * Available parameters:
   *
   * * memcache: A memcache object (optional)
   *
   * * host:       The default host (default to localhost)
   * * port:       The port for the default server (default to 11211)
   * * persistent: true if the connection must be persistent, false otherwise (true by default)
   *
   * * servers:    An array of additional servers (keys: host, port, persistent)
   *
   * * see sfCache for default parameters available for all drivers
   *
   * @see sfCache
   */
  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    if (!class_exists('Memcache'))
    {
      throw new sfInitializationException('You must have memcache installed and enabled to use sfMemcacheCache class.');
    }

    $this->prefix = md5(sfConfig::get('sf_app_dir')).self::SEPARATOR;

    if ($this->getParameter('memcache'))
    {
      $this->memcache = $this->getParameter('memcache');
    }
    else
    {
      $this->memcache = new Memcache();
      $method = $this->getParameter('persistent', true) ? 'pconnect' : 'connect';
      if (!$this->memcache->$method($this->getParameter('host', 'localhost'), $this->getParameter('port', 11211), $this->getParameter('timeout', 1)))
      {
        throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $this->getParameter('host', 'localhost'), $this->getParameter('port', 11211)));
      }

      if ($this->getParameter('servers'))
      {
        foreach ($this->getParameter('servers') as $server)
        {
          $port = isset($server['port']) ? $server['port'] : 11211;
          if (!$this->memcache->addServer($server['host'], $port, isset($server['persistent']) ? $server['persistent'] : true))
          {
            throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $server['host'], $port));
          }
        }
      }
    }
  }

  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->memcache;
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = $this->memcache->get($this->prefix.$key);

    return false === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return false === $this->memcache->get($this->prefix.$key) ? false : true;
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = is_null($lifetime) ? $this->getParameter('lifetime') : $lifetime;

    // save metadata
    $this->setMetadata($key, $lifetime);

    // save key for removePattern()
    if ($this->getParameter('storeCacheInfo', false))
    {
      $this->setCacheInfo($key);
    }

    return $this->memcache->set($this->prefix.$key, $data, false, $lifetime);
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    $this->memcache->delete($this->prefix.'_metadata'.self::SEPARATOR.$key);

    return $this->memcache->delete($this->prefix.$key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      return $this->memcache->flush();
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['lastModified'];
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['timeout'];
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    if (!$this->getParameter('storeCacheInfo', false))
    {
      throw new sfCacheException('To use the "removePattern" method, you must set the "storeCacheInfo" parameter to "true".');
    }

    $regexp = self::patternToRegexp($this->prefix.$pattern);

    foreach ($this->getCacheInfo() as $key)
    {
      if (preg_match($regexp, $key))
      {
        $this->memcache->delete($key);
      }
    }
  }

  /**
   * @see sfCache
   */
  public function getMany($keys)
  {
    $values = array();
    foreach ($this->memcache->get(array_map(create_function('$k', 'return "'.$this->prefix.'".$k;'), $keys)) as $key => $value)
    {
      $values[str_replace($this->prefix, '', $key)] = $value;
    }

    return $values;
  }

  /**
   * Gets metadata about a key in the cache.
   *
   * @param  string A cache key
   *
   * @return array  An array of metadata information
   */
  protected function getMetadata($key)
  {
    return $this->memcache->get($this->prefix.'_metadata'.self::SEPARATOR.$key);
  }

  /**
   * Stores metadata about a key in the cache.
   *
   * @param  string A cache key
   * @param  string The lifetime
   */
  protected function setMetadata($key, $lifetime)
  {
    $this->memcache->set($this->prefix.'_metadata'.self::SEPARATOR.$key, array('lastModified' => time(), 'timeout' => time() + $lifetime), false, $lifetime);
  }

  /**
   * Updates the cache information for the given cache key.
   *
   * @param string The cache key
   */
  protected function setCacheInfo($key)
  {
    $keys = $this->memcache->get($this->prefix.'_metadata');
    if (!is_array($keys))
    {
      $keys = array();
    }
    $keys[] = $this->prefix.$key;
    $this->memcache->set($this->prefix.'_metadata', $keys, 0);
  }

  /**
   * Gets cache information.
   *
   * @param array An array of cache keys
   */
  protected function getCacheInfo()
  {
    $keys = $this->memcache->get($this->prefix.'_metadata');
    if (!is_array($keys))
    {
      return array();
    }

    return $keys;
  }
}
