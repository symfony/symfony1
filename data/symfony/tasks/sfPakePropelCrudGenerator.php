<?php

pake_desc('initialize a new propel CRUD module');
pake_task('init-propelcrud', 'app_exists');

pake_desc('generate a new propel CRUD module');
pake_task('generate-propelcrud', 'app_exists');

function run_init_propelcrud($task, $args)
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
  pake_mirror($finder, PAKEFILE_SYMFONY_DATA_DIR.'/symfony/generator/sfPropelCrud/default/skeleton/', getcwd().'/'.$app.'/modules/'.$module);

  // create basic test
  pake_copy(PAKEFILE_DATA_DIR.'/symfony/skeleton/module/test/actionsTest.php', getcwd().'/test/'.$app.'/'.$module.'ActionsTest.php');

  // customize test file
  pake_replace_tokens($module.'ActionsTest.php', getcwd().'/test/'.$app, '##', '##', $constants);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, getcwd().'/'.$app.'/modules/'.$module, '##', '##', $constants);
}

function run_generate_propelcrud($task, $args)
{
  if (count($args) < 2)
  {
    throw new Exception('you must provide your module name');
  }

  if (count($args) < 3)
  {
    throw new Exception('you must provide your model class name');
  }

  if (!defined('SF_SYMFONY_DATA_DIR'))
  {
    define('SF_SYMFONY_DATA_DIR', PAKEFILE_DATA_DIR);
  }

  $theme = isset($args[3]) ? $args[3] : 'default';

  $app         = $args[0];
  $module      = $args[1];
  $model_class = $args[2];

  // model class exists?
  if (!is_readable('lib/model/'.$model_class.'.php'))
  {
    $error = 'the model class "%s" does not exist';
    $error = sprintf($error, $model_class);
    throw new Exception($error);
  }

  // generate module
  $tmp_dir = getcwd().DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
  define('SF_MODULE_CACHE_DIR', $tmp_dir);
  set_include_path(getcwd().PATH_SEPARATOR.getcwd().DIRECTORY_SEPARATOR.'lib'.PATH_SEPARATOR.PAKEFILE_LIB_DIR.PATH_SEPARATOR.get_include_path());
  require_once('symfony/exception/sfException.class.php');
  require_once('symfony/exception/sfInitializationException.class.php');
  require_once('symfony/exception/sfParseException.class.php');
  require_once('symfony/exception/sfConfigurationException.class.php');
  require_once('symfony/cache/sfCache.class.php');
  require_once('symfony/cache/sfFileCache.class.php');
  require_once('symfony/generator/sfGenerator.class.php');
  require_once('symfony/generator/sfGeneratorManager.class.php');
  require_once('symfony/generator/sfPropelCrudGenerator.class.php');
  require_once('symfony/util/sfInflector.class.php');
  require_once('propel/Propel.php');
  require_once('lib/model/'.$model_class.'.php');
  $generator_manager = new sfGeneratorManager();
  $generator_manager->initialize();
  $generator_manager->generate('sfPropelCrudGenerator', array('model_class' => $model_class, 'moduleName' => $module, 'theme' => $theme));

  // copy our generated module
  $finder = pakeFinder::type('any');
  pake_mirror($finder, $tmp_dir.'/auto'.ucfirst($module), getcwd().'/'.$app.'/modules/'.$module);

  // change module name
  pake_replace_tokens($app.'/modules/'.$module.'/actions/actions.class.php', getcwd(), '', '', array('auto'.ucfirst($module) => $module));

  // delete temp files
  $finder = pakeFinder::type('any');
  pake_remove($finder, $tmp_dir);
}

?>