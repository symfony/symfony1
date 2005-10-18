<?php

// define some PEAR directory constants
define('PAKEFILE_LIB_DIR',  '@PEAR-DIR@');
define('PAKEFILE_DATA_DIR', '@DATA-DIR@');
define('PAKEFILE_SYMLINK',  false);
define('SYMFONY_VERSION',   '@SYMFONY-VERSION@');

require_once 'pake.php';

// we trap -V before pake
require_once 'pake/pakeGetopt.class.php';
$OPTIONS = array(
  array('--version',  '-V', pakeGetopt::NO_ARGUMENT, ''),
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
    }
    exit(0);
  }
}
catch (Exception $e)
{
}

pakeApp::get_instance()->run(PAKEFILE_DATA_DIR.'/symfony/bin/pakefile.php');

?>