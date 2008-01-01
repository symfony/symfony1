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
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAutoload
{
  static protected
    $instance = null;

  protected
    $overriden = array(),
    $classes = array();

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfAutoload A sfAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfAutoload();
    }

    return self::$instance;
  }

  /**
   * Register sfAutoload in spl autoloader.
   *
   * @return void
   */
  public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');

    spl_autoload_register(array($this, 'autoload'));
  }

  /**
   * Unregister sfAutoload from spl autoloader.
   *
   * @return void
   */
  public function unregister()
  {
    spl_autoload_unregister(array($this, 'autoload'));
  }

  /**
   * Sets path to class.
   *
   * @param  string  A class name.
   * @param  string  Path to class.
   *
   * @return void
   */
  public function setClassPath($class, $path)
  {
    $this->overriden[$class] = $path;

    $this->classes[$class] = $path;
  }

  /**
   * Get path to class.
   *
   * @param  string  A class name.
   *
   * @return void
   */
  public function getClassPath($class)
  {
    return isset($this->classes[$class]) ? $this->classes[$class] : null;
  }

  /**
   * Reloads all registered classes.
   *
   * @param  boolean Force delete of autoload cache?
   *
   * @return void
   */
  public function reloadClasses($force = false)
  {
    if ($force)
    {
      @unlink(sfConfigCache::getInstance()->getCacheName(sfConfig::get('sf_app_config_dir_name').'/autoload.yml'));
    }

    $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');

    $this->classes = include($file);

    foreach ($this->overriden as $class => $path)
    {
      $this->classes[$class] = $path;
    }
  }

  /**
   * Handles autoloading of classes that have been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    // load the list of autoload classes
    if (!$this->classes)
    {
      self::reloadClasses();
    }

    if (self::loadClass($class))
    {
      return true;
    }

    return false;
  }

  /**
   * Reloads a class.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoloadAgain($class)
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
  public function loadClass($class)
  {
    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset($this->classes[$class]))
    {
      require($this->classes[$class]);

      return true;
    }

    // see if the file exists in the current module lib directory
    // must be in a module context
    if (sfContext::hasInstance() && ($module = sfContext::getInstance()->getModuleName()) && isset($this->classes[$module.'/'.$class]))
    {
      require($this->classes[$module.'/'.$class]);

      return true;
    }

    return false;
  }
}
