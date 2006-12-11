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
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProcessCache
{
  public static function cacher()
  {
    static $cacher = null;

    if (null === $cacher)
    {
      if (!sfConfig::get('sf_use_process_cache'))
      {
        $cacher = false;
      }
      elseif (function_exists('apc_store'))
      {
        $cacher = 'apc';
      }
      elseif (function_exists('xcache_set'))
      {
        $cacher = 'xcache';
      }
      elseif (function_exists('ecacher_put'))
      {
        $cacher = 'eaccelerator';
      }
      else
      {
        $cacher = false;
      }
    }

    return $cacher;
  }

  public static function getPrefix()
  {
    static $prefix = null;

    if (!$prefix)
    {
      $prefix = md5(sfConfig::get('sf_app_dir')).'_';
    }

    return $prefix;
  }

  public static function set($key, $value, $lifeTime = 0)
  {
    switch (self::cacher())
    {
      case 'apc':
        return apc_store(self::getPrefix().$key, $value, $lifeTime);
      case 'xcache':
        return xcache_set(self::getPrefix().$key, $value, $lifeTime);
      case 'eaccelerator':
        return eaccelerator_put(self::getPrefix().$key, serialize($value), $lifeTime);
    }

    return false;
  }

  public static function get($key)
  {
    switch (self::cacher())
    {
      case 'apc':
        $value = apc_fetch(self::getPrefix().$key);
        return false === $value ? null : $value;
      case 'xcache':
        return xcache_isset(self::getPrefix().$key) ? xcache_get(self::getPrefix().$key) : null;
      case 'eaccelerator':
        return unserialize(eaccelerator_get(self::getPrefix().$key));
    }

    return null;
  }

  public static function has($key)
  {
    switch (self::cacher())
    {
      case 'apc':
        return false === apc_fetch(self::getPrefix().$key) ? false : true;
      case 'xcache':
        return xcache_isset(self::getPrefix().$key);
      case 'eaccelerator':
        return null === eaccelerator_get(self::getPrefix().$key) ? false : true;
    }

    return false;
  }

  public static function clear()
  {
    switch (self::cacher())
    {
      case 'apc':
        return apc_clear_cache('user');
      case 'xcache':
        for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
        {
          if (!xcache_clear_cache(XC_TYPE_VAR, $i))
          {
            return false;
          }
        }
        return true;
      case 'eaccelerator':
        eaccelerator_clean();
    }

    return false;
  }
}
