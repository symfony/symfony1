<?php

// start timer
define('SF_TIMER_START', microtime(true));

// symfony directories
if (is_readable(SF_ROOT_DIR.'/lib/symfony'))
{
  // symlink exists
  define('SF_SYMFONY_LIB_DIR',  SF_ROOT_DIR.'/lib/symfony');
  define('SF_SYMFONY_DATA_DIR', SF_ROOT_DIR.'/data/symfony');
}
else
{
  // PEAR config
  if ((include('symfony/symfony/pear.php')) != 'OK')
  {
    throw new Exception('Unable to find symfony librairies');
  }
}

// directory layout
require(SF_SYMFONY_DATA_DIR.'/symfony/config/constants.php');

// include path
set_include_path(
  SF_LIB_DIR.PATH_SEPARATOR.
  SF_SYMFONY_LIB_DIR.PATH_SEPARATOR.
  SF_APP_LIB_DIR.PATH_SEPARATOR.
  SF_MODEL_DIR.PATH_SEPARATOR.
  get_include_path()
);

// check to see if we're not in a cache cleaning process
require_once('symfony/util/sfToolkit.class.php');
if (sfToolkit::hasLockFile(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.lck', 5))
{
  // application is not yet available
  include(SF_WEB_DIR.'/unavailable.html');
  die(1);
}

// require project configuration
require_once(dirname(__FILE__).'/../../config/config.php');

// test mode
@define('SF_TEST', false);

// go
$bootstrap = SF_CONFIG_CACHE_DIR.'/config_bootstrap_compile.yml.php';
if (is_readable($bootstrap))
{
  require_once($bootstrap);
}
else
{
  require_once 'symfony/symfony.php';
}

?>