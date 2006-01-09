<?php

pake_desc('initialize a new symfony project');
pake_task('init-project');
pake_alias('new', 'init-project');

pake_desc('initialize a new symfony application');
pake_task('init-app', 'project_exists');
pake_alias('app', 'init-app');

pake_desc('initialize a new symfony module');
pake_task('init-module', 'app_exists');
pake_alias('module', 'init-module');

function run_init_project($task, $args)
{
  if (file_exists('SYMFONY'))
  {
    throw new Exception('a symfony project already exists in this directory');
  }

  if (!count($args))
  {
    throw new Exception('you must provide a project name');
  }

  $project_name = $args[0];

  // create basic project structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/project', getcwd());

  $finder = pakeFinder::type('file')->name('properties.ini', 'apache.conf', 'propel.ini');
  pake_replace_tokens($finder, getcwd(), '##', '##', array('PROJECT_NAME' => $project_name));

  $finder = pakeFinder::type('file')->name('propel.ini');
  pake_replace_tokens($finder, getcwd(), '##', '##', array('PROJECT_DIR' => getcwd()));

  // create symlink if needed
  if (sfConfig::get('sf_symfony_symlink'))
  {
    pake_symlink(sfConfig::get('sf_symfony_lib_dir'),  getcwd().'/lib/symfony');
    pake_symlink(sfConfig::get('sf_symfony_data_dir'), getcwd().'/data/symfony');
  }

  run_fix_perms($task, $args);
}

function run_init_app($task, $args)
{
  if (!count($args))
  {
    throw new Exception('you must provide your application name');
  }

  $app = $args[0];

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/app/app', getcwd().'/apps/'.$app);

  // create $app.php or index.php if it is our first app
  $index_name = 'index';
  $first_app = file_exists(getcwd().'/web/index.php') ? false : true;
  if (!$first_app)
  {
    $index_name = $app;
  }

  // set no_script_name value in settings.yml for production environment
  $finder = pakeFinder::type('file')->name('settings.yml');
  pake_replace_tokens($finder, getcwd().'/apps/'.$app.'/config', '##', '##', array('NO_SCRIPT_NAME' => ($first_app ? 'on' : 'off')));

  pake_copy(sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/app/web/index.php', getcwd().'/web/'.$index_name.'.php');
  pake_copy(sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/app/web/index_dev.php', getcwd().'/web/'.$app.'_dev.php');

  $finder = pakeFinder::type('file')->name($index_name.'.php', $app.'_dev.php');
  pake_replace_tokens($finder, getcwd().'/web', '##', '##', array('APP_NAME' => $app));

  run_fix_perms($task, $args);

  // create test dir
  pake_mkdirs(getcwd().'/test/'.$app);
}

function run_init_module($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('you must provide your module name');
  }

  $app    = $args[0];
  $module = $args[1];

  $constants = array(
    'PROJECT_NAME' => $task->get_property('name', 'symfony'),
    'APP_NAME'     => $app,
    'MODULE_NAME'  => $module,
  );

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/module/module/', getcwd().'/apps/'.$app.'/modules/'.$module);

  // create basic test
  pake_copy(sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/module/test/actionsTest.php', getcwd().'/test/'.$app.'/'.$module.'ActionsTest.php');

  // customize test file
  pake_replace_tokens($module.'ActionsTest.php', getcwd().'/test/'.$app, '##', '##', $constants);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, getcwd().'/apps/'.$app.'/modules/'.$module, '##', '##', $constants);
}

?>