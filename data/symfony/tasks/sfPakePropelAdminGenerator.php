<?php

pake_desc('initialize a new propel admin module');
pake_task('init-propeladmin', 'app_exists');

function run_init_propeladmin($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('you must provide your module name');
  }

  if (count($args) < 3)
  {
    throw new Exception('you must provide your model class name');
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

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
  pake_mirror($finder, PAKEFILE_SYMFONY_DATA_DIR.'/symfony/generator/sfPropelAdmin/default/skeleton/', getcwd().'/'.$app.'/modules/'.$module);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, getcwd().'/'.$app.'/modules/'.$module, '##', '##', $constants);
}

?>