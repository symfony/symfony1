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
  require_once($sf_symfony_lib_dir.'/config/sfLoader.class.php');

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

$configCache = sfConfigCache::getInstance();

// get config instance
$sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');

// load base settings
include($configCache->checkConfig($sf_app_config_dir_name.'/settings.yml'));
if ($file = $configCache->checkConfig($sf_app_config_dir_name.'/app.yml', true))
{
  include($configCache->checkConfig($sf_app_config_dir_name.'/app.yml'));
}

$sf_debug = sfConfig::get('sf_debug');

// create bootstrap file for next time
if (!sfConfig::get('sf_in_bootstrap') && !$sf_debug && !sfConfig::get('sf_test'))
{
  $configCache->checkConfig($sf_app_config_dir_name.'/bootstrap_compile.yml');
}

// required core classes for the framework
// create a temp var to avoid substitution during compilation
if (!$sf_debug && !sfConfig::get('sf_test'))
{
  $core_classes = $sf_app_config_dir_name.'/core_compile.yml';
  $configCache->import($core_classes, false);
}
