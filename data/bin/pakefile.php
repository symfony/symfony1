<?php

// symfony directories
if (is_readable(dirname(__FILE__).'/../../lib/symfony.php'))
{
  // symlink exists
  $sf_symfony_lib_dir  = realpath(dirname(__FILE__).'/../../lib');
  $sf_symfony_data_dir = realpath(dirname(__FILE__).'/..');
  $symlink = true;
}
else if (is_readable(dirname(__FILE__).'/../../../lib/symfony/symfony.php'))
{
  // symlink exists
  $sf_symfony_lib_dir  = realpath(dirname(__FILE__).'/../../../lib/symfony');
  $sf_symfony_data_dir = realpath(dirname(__FILE__).'/..');
  $symlink = true;
}
else
{
  // PEAR config
  if ((include('symfony/pear.php')) != 'OK')
  {
    throw new Exception('Unable to find symfony librairies');
  }
  $symlink = false;
}

require_once($sf_symfony_lib_dir.'/config/sfConfig.class.php');

sfConfig::add(array(
  'sf_root_dir'         => getcwd(),
  'sf_symfony_lib_dir'  => $sf_symfony_lib_dir,
  'sf_symfony_data_dir' => $sf_symfony_data_dir,
  'sf_symfony_symlink'  => $symlink,
));

// directory layout
include($sf_symfony_data_dir.'/config/constants.php');

// include path
set_include_path(
  sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_model_dir').PATH_SEPARATOR.
  sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
  get_include_path()
);

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

  if (!is_dir(getcwd().'/apps/'.$args[0]))
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

// include tasks definitions
$dirs = array(
  sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'tasks' => 'myPake*.php', // project tasks
  $sf_symfony_data_dir.DIRECTORY_SEPARATOR.'tasks'         => 'sfPake*.php', // symfony tasks
  sfConfig::get('sf_root_dir').'/plugins/*/data/tasks'     => '*.php',       // plugin tasks
);
foreach ($dirs as $globDir => $name)
{
  $tasks = pakeFinder::type('file')->name($name)->in(glob($globDir));
  foreach ($tasks as $task)
  {
    include_once($task);
  }
}
