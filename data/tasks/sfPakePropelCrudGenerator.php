<?php

pake_desc('initialize a new propel CRUD module');
pake_task('init-propelcrud', 'app_exists');

pake_desc('generate a new propel CRUD module');
pake_task('generate-propelcrud', 'app_exists');

function run_init_propelcrud($task, $args)
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

  $sf_root_dir = sfConfig::get('sf_root_dir');
  $moduleDir   = $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_module_dir_name').'/'.$module;

  // create basic application structure
  $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn', '.sf');
  pake_mirror($finder, sfConfig::get('sf_symfony_data_dir').'/generator/sfPropelCrud/default/skeleton/', $moduleDir);

  // create basic test
  pake_copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/module/test/actionsTest.php', $sf_root_dir.'/test/'.$app.'/'.$module.'ActionsTest.php');

  // customize test file
  pake_replace_tokens($module.'ActionsTest.php', $sf_root_dir.'/test/'.$app, '##', '##', $constants);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, $moduleDir, '##', '##', $constants);
}

function run_generate_propelcrud($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('You must provide your module name.');
  }

  if (count($args) < 3)
  {
    throw new Exception('You must provide your model class name.');
  }

  $theme = isset($args[3]) ? $args[3] : 'default';

  $app         = $args[0];
  $module      = $args[1];
  $model_class = $args[2];

  // model class exists?
  if (!is_readable('lib/model/'.$model_class.'.php'))
  {
    $error = sprintf('The model class "%s" does not exist.', $model_class);
    throw new Exception($error);
  }

  $sf_root_dir = sfConfig::get('sf_root_dir');

  // generate module
  $tmp_dir = $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
  sfConfig::set('sf_module_cache_dir', $tmp_dir);
  $sf_symfony_lib_dir = sfConfig::get('sf_symfony_lib_dir');
  require_once($sf_symfony_lib_dir.'/config/sfConfig.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfInitializationException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfParseException.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfConfigurationException.class.php');
  require_once($sf_symfony_lib_dir.'/cache/sfCache.class.php');
  require_once($sf_symfony_lib_dir.'/cache/sfFileCache.class.php');
  require_once($sf_symfony_lib_dir.'/generator/sfGenerator.class.php');
  require_once($sf_symfony_lib_dir.'/generator/sfGeneratorManager.class.php');
  require_once($sf_symfony_lib_dir.'/generator/sfPropelCrudGenerator.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfToolkit.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfFinder.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfInflector.class.php');
  require_once($sf_symfony_lib_dir.'/vendor/propel/Propel.php');
  require_once('lib/model/'.$model_class.'.php');
  $generator_manager = new sfGeneratorManager();
  $generator_manager->initialize();
  $generator_manager->generate('sfPropelCrudGenerator', array('model_class' => $model_class, 'moduleName' => $module, 'theme' => $theme));

  $moduleDir = $sf_root_dir.'/'.sfConfig::get('sf_apps_dir_name').'/'.$app.'/'.sfConfig::get('sf_app_module_dir_name').'/'.$module;

  // copy our generated module
  $finder = pakeFinder::type('any');
  pake_mirror($finder, $tmp_dir.'/auto'.ucfirst($module), $moduleDir);

  // change module name
  pake_replace_tokens($moduleDir.'/actions/actions.class.php', getcwd(), '', '', array('auto'.ucfirst($module) => $module));

  // delete temp files
  $finder = pakeFinder::type('any');
  pake_remove($finder, $tmp_dir);
}

?>