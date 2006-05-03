<?php

// symfony directories
if (is_readable(SF_ROOT_DIR.'/lib/symfony/symfony.php'))
{
  // symlink exists
  $sf_symfony_lib_dir  = SF_ROOT_DIR.'/lib/symfony';
  $sf_symfony_data_dir = SF_ROOT_DIR.'/data/symfony';
  $sf_version          = '@DEV@';
}
else
{
  // PEAR config
  if ((include('symfony/pear.php')) != 'OK')
  {
    throw new Exception('Unable to find symfony librairies');
  }
}

require_once($sf_symfony_lib_dir.'/config/sfConfig.class.php');

sfConfig::add(array(
  'sf_root_dir'         => SF_ROOT_DIR,
  'sf_app'              => SF_APP,
  'sf_environment'      => SF_ENVIRONMENT,
  'sf_debug'            => SF_DEBUG,
  'sf_symfony_lib_dir'  => $sf_symfony_lib_dir,
  'sf_symfony_data_dir' => $sf_symfony_data_dir,
  'sf_test'             => false,
  'sf_version'          => $sf_version,
));

// start timer
if (sfConfig::get('sf_debug'))
{
  sfConfig::set('sf_timer_start', microtime(true));
}

// directory layout
include($sf_symfony_data_dir.'/config/constants.php');

// require project configuration
require_once(sfConfig::get('sf_config_dir').'/config.php');

// include path
set_include_path(
  sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
  get_include_path()
);

// check to see if we're not in a cache cleaning process
require_once(sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php');
if (sfToolkit::hasLockFile(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.lck', 5))
{
  // application is not yet available
  include(SF_WEB_DIR.'/unavailable.html');
  die(1);
}

// recent symfony update?
$version = @file_get_contents(sfConfig::get('sf_config_cache_dir').'/VERSION');
if ($version != $sf_version)
{
  // force cache regeneration
  foreach (array(sfConfig::get('sf_config_cache_dir').'/config_bootstrap_compile.yml.php', sfConfig::get('sf_config_cache_dir').'/config_core_compile.yml.php') as $file)
  {
    if (is_readable($file))
    {
      unlink($file);
    }
  }
}

// go
$bootstrap = sfConfig::get('sf_config_cache_dir').'/config_bootstrap_compile.yml.php';
if (is_readable($bootstrap))
{
  sfConfig::set('sf_in_bootstrap', true);
  require_once($bootstrap);
}
else
{
  require_once(sfConfig::get('sf_symfony_lib_dir').'/symfony.php');
}

?>