<?php

// PEAR?
if (!defined('PAKEFILE_LIB_DIR'))
{
  $base_dir = realpath(dirname(__FILE__).'/../../..');
  define('PAKEFILE_LIB_DIR',  $base_dir.'/lib');
  define('PAKEFILE_DATA_DIR', $base_dir.'/data');
  define('PAKEFILE_SYMLINK',  true);

  define('PAKEFILE_SYMFONY_LIB_DIR', PAKEFILE_LIB_DIR);
}
else
{
  define('PAKEFILE_SYMFONY_LIB_DIR', PAKEFILE_LIB_DIR.'/symfony');
}

define('PAKEFILE_SYMFONY_DATA_DIR', PAKEFILE_DATA_DIR);

set_include_path(PAKEFILE_SYMFONY_LIB_DIR.PATH_SEPARATOR.get_include_path());

/* tasks registration */
pake_task('project_exists');
pake_task('app_exists', 'project_exists');
pake_task('module_exists', 'app_exists');

/* tasks definition */
function run_fix()
{
  // noop
}

function run_project_exists($task, $args)
{
  if (!file_exists('SYMFONY'))
  {
    throw new Exception('you must be in a symfony project directory');
  }

  pake_properties('config/properties.ini');
}

function run_app_exists($task, $args)
{
  if (!count($args))
  {
    throw new Exception('you must provide your application name');
  }

  if (!is_dir(getcwd().'/'.$args[0]))
  {
    throw new Exception('application "'.$args[0].'" does not exist');
  }
}

function run_module_exists($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('you must provide your module name');
  }

  if (!is_dir(getcwd().'/'.$args[0].'/modules/'.$args[1]))
  {
    throw new Exception('module "'.$args[1].'" does not exist');
  }
}

/* include all tasks definitions */
$tasks = pakeFinder::type('file')->name('sfPake*.php')->in(realpath(dirname(__FILE__).'/..').DIRECTORY_SEPARATOR.'tasks');
foreach ($tasks as $task)
{
  include_once($task);
}

?>