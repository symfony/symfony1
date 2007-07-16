<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAutoload class.
 *
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAutoload
{
  static protected
    $classes = array();

  static public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');

    spl_autoload_register(array('sfAutoload', 'autoload'));
  }

  static public function unregister()
  {
    spl_autoload_unregister(array('sfAutoload', 'autoload'));
  }

  static public function getClassPath($class)
  {
    return isset(self::$classes[$class]) ? self::$classes[$class] : null;
  }

  static public function reloadClasses($force = false)
  {
    if ($force)
    {
      @unlink(sfConfigCache::getInstance()->getCacheName(sfConfig::get('sf_app_config_dir_name').'/autoload.yml'));
    }

    $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');

    self::$classes = include($file);
  }

  /**
   * Handles autoloading of classes that have been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function autoload($class)
  {
    // load the list of autoload classes
    if (!self::$classes)
    {
      self::reloadClasses();
    }

    if (self::loadClass($class))
    {
      return true;
    }

    return false;
  }

  static function autoloadAgain($class)
  {
    self::reloadClasses(true);

    return self::loadClass($class);
  }

  /**
   * Tries to load a class that has been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function loadClass($class)
  {
    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset(self::$classes[$class]))
    {
      require(self::$classes[$class]);

      return true;
    }

    // see if the file exists in the current module lib directory
    // must be in a module context
    if (sfContext::hasInstance() && ($module = sfContext::getInstance()->getModuleName()) && isset(self::$classes[$module.'/'.$class]))
    {
      require(self::$classes[$module.'/'.$class]);

      return true;
    }

    return false;
  }
}
