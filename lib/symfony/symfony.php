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

if (!sfConfig::get('sf_in_bootstrap'))
{
  include_once(dirname(__FILE__).'/symfony_autoload.php');
}

try
{
  ini_set('unserialize_callback_func', '__autoload');

  // get config instance
  $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');

  // create bootstrap file for next time
  if (!sfConfig::get('sf_in_bootstrap') && !sfConfig::get('sf_debug') && !sfConfig::get('sf_test'))
  {
    sfConfigCache::checkConfig($sf_app_config_dir_name.'/bootstrap_compile.yml');
  }

  // set exception format
  sfException::setFormat(isset($_SERVER['HTTP_HOST']) ? 'html' : 'plain');

  if (sfConfig::get('sf_debug'))
  {
    // clear our config and module cache
    sfConfigCache::clear();
  }

  // load base settings
  include(sfConfigCache::checkConfig($sf_app_config_dir_name.'/logging.yml'));
  sfConfigCache::import($sf_app_config_dir_name.'/php.yml');
  include(sfConfigCache::checkConfig($sf_app_config_dir_name.'/settings.yml'));
  include(sfConfigCache::checkConfig($sf_app_config_dir_name.'/app.yml'));

  // error settings
  ini_set('display_errors', sfConfig::get('sf_debug') ? 'on' : 'off');
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
  if (!sfConfig::get('sf_debug') && !sfConfig::get('sf_test'))
  {
    $core_classes = $sf_app_config_dir_name.'/core_compile.yml';
    sfConfigCache::import($core_classes);
  }

  if (sfConfig::get('sf_routing'))
  {
    sfConfigCache::import($sf_app_config_dir_name.'/routing.yml');
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