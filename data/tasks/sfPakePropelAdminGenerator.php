<?php

pake_desc('initialize a new propel admin module');
pake_task('propel-init-admin', 'app_exists');

function run_propel_init_admin($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('You must provide your module name.');
  }

  if (count($args) < 3)
  {
    throw new Exception('You must provide your model class name.');
  }

  $app         = $args[0];
  $module      = $args[1];
  $model_class = $args[2];

  $constants = array(
    'PROJECT_NAME' => $task->get_property('name', 'symfony'),
    'APP_NAME'     => $app,
    'MODULE_NAME'  => $module,
    'MODEL_CLASS'  => $model_class,
  );

  $moduleDir = sfConfig::get('sf_root_dir').'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_module_dir_name').'/'.$module;

  // create basic application structure
  $finder = pakeFinder::type('any')->ignore_version_control()->discard('.sf');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/generator/sfPropelAdmin/default/skeleton/', $moduleDir);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, $moduleDir, '##', '##', $constants);
}
