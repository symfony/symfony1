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
    throw new Exception('A symfony project already exists in this directory.');
  }

  if (!count($args))
  {
    throw new Exception('You must provide a project name.');
  }

  $project_name = $args[0];

  $sf_root_dir = sfConfig::get('sf_root_dir');

  // create basic project structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn', '.sf');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/skeleton/project', $sf_root_dir);

  $finder = pakeFinder::type('file')->name('properties.ini', 'apache.conf', 'propel.ini');
  pake_replace_tokens($finder, $sf_root_dir, '##', '##', array('PROJECT_NAME' => $project_name));

  $finder = pakeFinder::type('file')->name('propel.ini');
  pake_replace_tokens($finder, $sf_root_dir, '##', '##', array('PROJECT_DIR' => $sf_root_dir));

  // create symlink if needed
  if (sfConfig::get('sf_symfony_symlink'))
  {
    pake_symlink(sfConfig::get('sf_symfony_lib_dir'),  $sf_root_dir.'/lib/symfony');
    pake_symlink(sfConfig::get('sf_symfony_data_dir'), $sf_root_dir.'/data/symfony');
  }

  run_fix_perms($task, $args);
}

function run_init_app($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide your application name.');
  }

  $app = $args[0];

  $sf_root_dir = sfConfig::get('sf_root_dir');

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn', '.sf');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/skeleton/app/app', $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app);

  // create $app.php or index.php if it is our first app
  $index_name = 'index';
  $first_app = file_exists(sfConfig::get('sf_web_dir').'/index.php') ? false : true;
  if (!$first_app)
  {
    $index_name = $app;
  }

  // set no_script_name value in settings.yml for production environment
  $finder = pakeFinder::type('file')->name('settings.yml');
  pake_replace_tokens($finder, $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_config_dir_name'), '##', '##', array('NO_SCRIPT_NAME' => ($first_app ? 'on' : 'off')));

  pake_copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/app/web/index.php', sfConfig::get('sf_web_dir').'/'.$index_name.'.php');
  pake_copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/app/web/index_dev.php', sfConfig::get('sf_web_dir').'/'.$app.'_dev.php');

  $finder = pakeFinder::type('file')->name($index_name.'.php', $app.'_dev.php');
  pake_replace_tokens($finder, sfConfig::get('sf_web_dir'), '##', '##', array('APP_NAME' => $app));

  run_fix_perms($task, $args);

  // create test dir
  pake_mkdirs($sf_root_dir.'/test/'.$app);
}

function run_init_module($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('You must provide your module name.');
  }

  $app    = $args[0];
  $module = $args[1];

  $constants = array(
    'PROJECT_NAME' => $task->get_property('name', 'symfony'),
    'APP_NAME'     => $app,
    'MODULE_NAME'  => $module,
  );

  $sf_root_dir = sfConfig::get('sf_root_dir');

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn', '.sf');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/skeleton/module/module/', $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_module_dir_name').'/'.$module);

  // create basic test
  pake_copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/module/test/actionsTest.php', $sf_root_dir.'/test/'.$app.'/'.$module.'ActionsTest.php');

  // customize test file
  pake_replace_tokens($module.'ActionsTest.php', $sf_root_dir.'/test/'.$app, '##', '##', $constants);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_module_dir_name').'/'.$module, '##', '##', $constants);
}
