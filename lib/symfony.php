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

$sf_symfony_lib_dir = sfConfig::get('sf_symfony_lib_dir');
if (!sfConfig::get('sf_in_bootstrap'))
{
  // YAML support
  if (!function_exists('syck_load'))
  {
    require_once($sf_symfony_lib_dir.'/util/Spyc.class.php');
  }
  require_once($sf_symfony_lib_dir.'/util/sfYaml.class.php');

  // cache support
  require_once($sf_symfony_lib_dir.'/cache/sfCache.class.php');
  require_once($sf_symfony_lib_dir.'/cache/sfFileCache.class.php');

  // config support
  require_once($sf_symfony_lib_dir.'/config/sfConfigCache.class.php');
  require_once($sf_symfony_lib_dir.'/config/sfConfigHandler.class.php');
  require_once($sf_symfony_lib_dir.'/config/sfYamlConfigHandler.class.php');
  require_once($sf_symfony_lib_dir.'/config/sfAutoloadConfigHandler.class.php');
  require_once($sf_symfony_lib_dir.'/config/sfRootConfigHandler.class.php');

  // basic exception classes
  require_once($sf_symfony_lib_dir.'/exception/sfException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfAutoloadException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfCacheException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfConfigurationException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfParseException.class.php');

  // utils
  require_once($sf_symfony_lib_dir.'/util/sfParameterHolder.class.php');
}
else
{
  require_once($sf_symfony_lib_dir.'/config/sfConfigCache.class.php');
}

final class Symfony
{
  protected static $classes = array();

  public static function getClassPath($class)
  {
    return isset(self::$classes[$class]) ? self::$classes[$class] : null;
  }

  public static function __autoload($class)
  {
    if (!self::$classes)
    {
      // load the list of autoload classes
      $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');
      self::$classes = include($file);
    }

    if (!isset(self::$classes[$class]))
    {
      if (sfContext::hasInstance())
      {
        // see if the file exists in the current module lib directory
        // must be in a module context
        $current_module = sfContext::getInstance()->getModuleName();
        if ($current_module)
        {
          $module_lib = sfConfig::get('sf_app_module_dir').'/'.$current_module.'/'.sfConfig::get('sf_app_module_lib_dir_name').'/'.$class.'.class.php';
          if (is_readable($module_lib))
          {
            require_once($module_lib);

            return true;
          }
        }
      }

      return false;
    }
    else
    {
      // class exists, let's include it
      require_once(self::$classes[$class]);

      return true;
    }
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
  array_unshift($functions, array('Symfony', '__autoload'));

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
      array_unshift($functions, array('Symfony', '__autoload'));
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

try
{
  $configCache = sfConfigCache::getInstance();

  // force setting default timezone if not set
  if (function_exists('date_default_timezone_get'))
  {
    if ($default_timezone = sfConfig::get('sf_default_timezone'))
    {
      date_default_timezone_set($default_timezone);
    }
    else if (sfConfig::get('sf_force_default_timezone', true))
    {
      date_default_timezone_set(@date_default_timezone_get());
    }
  }

  // get config instance
  $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');

  $sf_debug = sfConfig::get('sf_debug');

  // set exception format
  sfException::setFormat(isset($_SERVER['HTTP_HOST']) ? 'html' : 'plain');

  // load base settings
  include($configCache->checkConfig($sf_app_config_dir_name.'/logging.yml'));
  $configCache->import($sf_app_config_dir_name.'/php.yml', false);
  include($configCache->checkConfig($sf_app_config_dir_name.'/settings.yml'));
  include($configCache->checkConfig($sf_app_config_dir_name.'/app.yml'));

  // create bootstrap file for next time
  if (!sfConfig::get('sf_in_bootstrap') && !$sf_debug && !sfConfig::get('sf_test'))
  {
    $configCache->checkConfig($sf_app_config_dir_name.'/bootstrap_compile.yml');
  }

  // error settings
  ini_set('display_errors', $sf_debug ? 'on' : 'off');
  error_reporting(sfConfig::get('sf_error_reporting'));

  // compress output
  ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : '');

  // required core classes for the framework
  // create a temp var to avoid substitution during compilation
  if (!$sf_debug && !sfConfig::get('sf_test'))
  {
    $core_classes = $sf_app_config_dir_name.'/core_compile.yml';
    $configCache->import($core_classes, false);
  }

  $configCache->import($sf_app_config_dir_name.'/routing.yml', false);
}
catch (sfException $e)
{
  $e->printStackTrace();
}
catch (Exception $e)
{
  // wrap non symfony exceptions
  $sfException = new sfException();
  $sfException->printStackTrace($e);
}
