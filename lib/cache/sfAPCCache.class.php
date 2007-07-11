<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in APC.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAPCCache extends sfCache
{
  protected $prefix = '';

  /**
   * Initializes this sfCache instance.
   *
   * Available parameters:
   *
   * * see sfCache for default parameters available for all drivers
   *
   * @see sfCache
   */
  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    if (!function_exists('apc_store') || !ini_get('apc.enabled'))
    {
      throw new sfInitializationException('You must have APC installed and enabled to use sfAPCCache class.');
    }

    $this->prefix = md5(sfConfig::get('sf_app_dir')).self::SEPARATOR;
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = apc_fetch($this->prefix.$key);

    return false === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return false === apc_fetch($this->prefix.$key) ? false : true;
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = is_null($lifetime) ? $this->getParameter('lifetime') : $lifetime;

    return apc_store($this->prefix.$key, $data, $lifetime);
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return apc_delete($this->prefix.$key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      return apc_clear_cache('user');
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['mtime'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['creation_time'] + $info['ttl'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $infos = apc_cache_info('user');
    if (!is_array($infos['cache_list']))
    {
      return;
    }

    $regexp = self::patternToRegexp($this->prefix.$pattern);

    foreach ($infos['cache_list'] as $info)
    {
      if (preg_match($regexp, $info['info']))
      {
        apc_delete($info['info']);
      }
    }
  }

  protected function getCacheInfo($key)
  {
    $infos = apc_cache_info('user');

    if (is_array($infos['cache_list']))
    {
      foreach ($infos['cache_list'] as $info)
      {
        if ($this->prefix.$key == $info['info'])
        {
          return $info;
        }
      }
    }

    return null;
  }
}
