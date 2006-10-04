<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pre-initialization script.
 *
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */

class sfAutoload
{
  protected static $classes = array();

  public static function getClassPath($class)
  {
    return isset(self::$classes[$class]) ? self::$classes[$class] : null;
  }

  public static function __autoload($class)
  {
    // load the list of autoload classes
    if (!self::$classes)
    {
      $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');
      self::$classes = include($file);
    }

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
}

/**
 * Handles autoloading of classes that have been specified in autoload.yml.
 *
 * @param string A class name.
 *
 * @return void
 */
if (function_exists('spl_autoload_register'))
{
  ini_set('unserialize_callback_func', 'spl_autoload_call');

  // load functions and methods that can autoload classes
  $functions = (array) sfConfig::get('sf_autoloading_functions', array());
  array_unshift($functions, array('sfAutoload', '__autoload'));

  foreach ($functions as $function)
  {
    spl_autoload_register($function);
  }
  unset($functions);

}
elseif (!function_exists('__autoload'))
{
  ini_set('unserialize_callback_func', '__autoload');

  function __autoload($class)
  {
    static $functions = null;

    if (null === $functions)
    {
      // load functions and methods that can autoload classes
      $functions = (array) sfConfig::get('sf_autoloading_functions', array());
      array_unshift($functions, array('sfAutoload', '__autoload'));
    }

    foreach ($functions as $function)
    {
      if (call_user_func($function, $class))
      {
        return true;
      }
    }

    // unspecified class

    // do not print an error if the autoload came from class_exists
    $trace = debug_backtrace();
    if (count($trace) < 1 || ($trace[1]['function'] != 'class_exists' && $trace[1]['function'] != 'is_a'))
    {
      $error = sprintf('Autoloading of class "%s" failed. Try to clear the symfony cache and refresh. [err0003]', $class);
      $e = new sfAutoloadException($error);

      $e->printStackTrace();
    }
  }
}
