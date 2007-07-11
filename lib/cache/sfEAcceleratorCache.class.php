<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in EAccelerator.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfEAcceleratorCache extends sfCache
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

    if (!function_exists('eaccelerator_put') || !ini_get('eaccelerator.enable'))
    {
      throw new sfInitializationException('You must have EAccelerator installed and enabled to use sfEAcceleratorCache class (or perhaps you forgot to add --with-eaccelerator-shared-memory when installing).');
    }

    $this->prefix = md5(sfConfig::get('sf_app_dir')).self::SEPARATOR;
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = eaccelerator_get($this->prefix.$key);

    return is_null($value) ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return null === eaccelerator_get($this->prefix.$key) ? false : true;
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = is_null($lifetime) ? $this->getParameter('lifetime') : $lifetime;

    return eaccelerator_put($this->prefix.$key, $data, $lifetime);
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return eaccelerator_rm($this->prefix.$key);
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $infos = eaccelerator_list_keys();

    if (is_array($infos))
    {
      $regexp = self::patternToRegexp($this->prefix.$pattern);

      foreach ($infos as $info)
      {
        if (preg_match($regexp, $info['name']))
        {
          eaccelerator_rm($this->prefix.$key);
        }
      }
    }
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::OLD == $mode)
    {
      return eaccelerator_gc();
    }

    eaccelerator_clean();
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['created'];
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
      return -1 == $info['ttl'] ? 0 : $info['created'] + $info['ttl'];
    }

    return 0;
  }

  protected function getCacheInfo($key)
  {
    $infos = eaccelerator_list_keys();

    if (is_array($infos))
    {
      foreach ($infos as $info)
      {
        if ($this->prefix.$key == $info['name'])
        {
          return $info;
        }
      }
    }

    return null;
  }
}
