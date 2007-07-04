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

  static public function initAutoload()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');

    spl_autoload_register(array('sfAutoload', 'autoload'));
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
    if (class_exists($class, false))
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

  static public function splSimpleAutoload($class)
  {
    // class already exists
    if (class_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset(self::$classes[$class]))
    {
      require(self::$classes[$class]);

      return true;
    }

    return false;
  }

  static public function initSimpleAutoload($dirs)
  {
    require_once(dirname(__FILE__).'/sfFinder.class.php');
    self::$classes = array();
    $finder = sfFinder::type('file')->ignore_version_control()->name('*.php');
    foreach ((array) $dirs as $dir)
    {
      $files = $finder->in(glob($dir));
      if (is_array($files))
      {
        foreach ($files as $file)
        {
          preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
          foreach ($classes[1] as $class)
          {
            self::$classes[$class] = $file;
          }
        }
      }
    }

    ini_set('unserialize_callback_func', 'spl_autoload_call');
    spl_autoload_register(array('sfCore', 'splSimpleAutoload'));
  }
}
