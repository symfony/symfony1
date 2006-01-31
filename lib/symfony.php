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

include_once(dirname(__FILE__).'/symfony_autoload.php');

try
{
  $configCache = sfConfigCache::getInstance();

  ini_set('unserialize_callback_func', '__autoload');

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

  // load base settings
  include($configCache->checkConfig($sf_app_config_dir_name.'/logging.yml'));
  $configCache->import($sf_app_config_dir_name.'/php.yml');
  include($configCache->checkConfig($sf_app_config_dir_name.'/settings.yml'));
  include($configCache->checkConfig($sf_app_config_dir_name.'/app.yml'));

  // set exception format
  sfException::setFormat(isset($_SERVER['HTTP_HOST']) ? 'html' : 'plain');

  $sf_debug = sfConfig::get('sf_debug');

  if ($sf_debug)
  {
    // clear our config and module cache
    $configCache->clear();
  }

  // error settings
  ini_set('display_errors', $sf_debug ? 'on' : 'off');
  error_reporting(sfConfig::get('sf_error_reporting'));

  // compress output
  ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : '');

/*
  if (sfConfig::get('sf_logging_active'))
  {
    set_error_handler(array('sfLogger', 'errorHandler'));
  }
*/

  // required core classes for the framework
  // create a temp var to avoid substitution during compilation
  if (!$sf_debug && !sfConfig::get('sf_test'))
  {
    $core_classes = $sf_app_config_dir_name.'/core_compile.yml';
    $configCache->import($core_classes);
  }

  if (sfConfig::get('sf_routing'))
  {
    // we cannot cache the routing rules because of configuration problem
    $routing = $sf_app_config_dir_name.'/routing.yml';
    $configCache->import($routing);
  }
}
catch (sfException $e)
{
  $e->printStackTrace();
}
catch (Exception $e)
{
  // unknown exception
  $e = new sfException($e->getMessage());

  $e->printStackTrace();
}

?>