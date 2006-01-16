<?php

if (ini_get('zend.ze1_compatibility_mode'))
{
  die("symfony cannot run with zend.ze1_compatibility_mode enabled.\nPlease turn zend.ze1_compatibility_mode to Off in your php.ini.\n");
}

// define some PEAR directory constants
define('PAKEFILE_LIB_DIR',  '@PEAR-DIR@');
define('PAKEFILE_DATA_DIR', '@DATA-DIR@');
define('PAKEFILE_SYMLINK',  false);
define('SYMFONY_VERSION',   '@SYMFONY-VERSION@');

require_once 'pake.php';

// we trap -V before pake
require_once 'pake/pakeGetopt.class.php';
$OPTIONS = array(
  array('--version', '-V', pakeGetopt::NO_ARGUMENT, ''),
  array('--pakefile', '-f', pakeGetopt::OPTIONAL_ARGUMENT, ''),
);
$opt = new pakeGetopt($OPTIONS);
try
{
  $opt->parse();

  foreach ($opt->get_options() as $opt => $value)
  {
    if ($opt == 'version')
    {
      echo 'symfony version '.SYMFONY_VERSION."\n";
      exit(0);
    }
  }
}
catch (pakeException $e)
{
  print $e->getMessage();
}

// find pakefile (local or PEAR)
if (is_readable('lib/symfony'))
{
  // local
  $pakefile = 'data/symfony/symfony/bin/pakefile.php';
}
else
{
  // PEAR
  $pakefile = PAKEFILE_DATA_DIR.'/symfony/bin/pakefile.php';
}

$pake = pakeApp::get_instance();
try
{
  $pake->run($pakefile);
}
catch (pakeException $e)
{
  echo "ERROR: symfony - ".$e->getMessage()."\n";
}

?>