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

pake_desc('clear cached information');
pake_task('clear-cache', 'project_exists');
pake_alias('cc', 'clear-cache');

pake_desc('initialize a new symfony project');
pake_task('init-project');
pake_alias('new', 'init-project');

pake_desc('initialize a new symfony application');
pake_task('init-app', 'project_exists');
pake_alias('app', 'init-app');

pake_desc('initialize a new symfony module');
pake_task('init-module', 'app_exists');
pake_alias('module', 'init-module');

pake_desc('initialize a new propel CRUD module');
pake_task('init-propelcrud', 'app_exists');

pake_desc('generate a new propel CRUD module');
pake_task('generate-propelcrud', 'app_exists');

pake_desc('backup uploaded datas');
pake_task('backup-data', 'project_exists');

pake_desc('fix directories permissions');
pake_task('fix-perms', 'project_exists');

pake_desc('create classes for current model');
pake_task('build-model', 'project_exists');

pake_desc('create sql for current model');
pake_task('build-sql', 'project_exists');

pake_desc('synchronise project with another machine');
pake_task('sync', 'project_exists');

pake_desc('launch project test suite');
pake_task('test');

/* tasks definition */
function run_test($task, $args)
{
  if (!count($args))
  {
    throw new Exception('you must provide the app to test');
  }

  $app = $args[0];

  if (!is_dir($app))
  {
    throw new Exception('you must provide the app to test');
  }

  // define constants
  define('SF_ROOT_DIR',    getcwd());
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', 'test');
  define('SF_DEBUG',       true);

  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

  $dirs_to_test = array($app);
  if (is_dir('test/project'))
  {
    $dirs_to_test[] = 'project';
  }

  pake_import('simpletest', false);
  pakeSimpletestTask::call_simpletest($task, 'text', $dirs_to_test);
}

function run_fix()
{
  // noop
}

function run_sync($task, $args)
{
  if (!count($args))
  {
    throw new Exception('you must provide an environment to synchronize');
  }

  $env = $args[0];

  $dryrun = isset($args[1]) ? $args[1] : false;

  if (!file_exists('config/rsync_exclude.txt'))
  {
    throw new Exception('you must create a rsync_exclude file for your project');
  }

  $host = $task->get_property('host', $env);
  if (!$host)
  {
    throw new Exception('you must set "host" variable in your properties.ini file');
  }

  $user = $task->get_property('user', $env);
  if (!$user)
  {
    throw new Exception('you must set "user" variable in your properties.ini file');
  }

  $dir = $task->get_property('dir', $env);
  if (!$dir)
  {
    throw new Exception('you must set "dir" variable in your properties.ini file');
  }

  if (substr($dir, -1) != '/')
  {
    $dir .= '/';
  }

  $ssh = 'ssh';

  $port = $task->get_property('port', $env);
  if ($port)
  {
    $ssh = '"ssh -p'.$port.'"';
  }

  $dry_run = ($dryrun == 'go' || $dryrun == 'ok') ? '' : '--dry-run';
  $cmd = "rsync --progress $dry_run -azC --exclude-from=config/rsync_exclude.txt --force --delete -e $ssh ./ $user@$host:$dir";

  echo pake_sh($cmd);
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

function run_build_model($task, $args)
{
  _call_phing($task, 'build-om');
}

function run_build_sql($task, $args)
{
  _call_phing($task, 'build-sql');
}

function _call_phing($task, $task_name, $check_schema = true)
{
  if ($check_schema && !file_exists('config/schema.xml'))
  {
    throw new Exception('you must create a schema.xml file');
  }

  // FIXME: we update propel.ini with uptodate values

  $propel_generator_dir = PAKEFILE_SYMFONY_LIB_DIR.'/propel-generator';

  // call phing targets
  pake_import('Phing', false);
  pakePhingTask::call_phing($task, array($task_name), dirname(__FILE__).'/build.xml', array('project' => $task->get_property('name', 'symfony'), 'lib_dir' => PAKEFILE_LIB_DIR, 'data_dir' => PAKEFILE_DATA_DIR, 'propel_generator_dir' => $propel_generator_dir));
}

function run_fix_perms($task, $args)
{
  $finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn');
  pake_chmod($finder, getcwd().'/web/uploads', 0777);
  pake_chmod('log', getcwd(), 0777);
  pake_chmod('cache', getcwd(), 0777);

  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn');
  pake_chmod($finder, getcwd().'/web/uploads', 0666);
  pake_chmod($finder, getcwd().'/log', 0666);
}

function run_clear_cache($task, $args)
{
  if (!file_exists('cache'))
  {
    throw new Exception('cache directory does not exist');
  }

  $cache_dir = 'cache';

  // app
  $main_app = '';
  if (isset($args[0]))
  {
    $main_app = $args[0];
  }

  // type (template, i18n or config)
  $main_type = '';
  if (isset($args[1]))
  {
    $main_type = $args[1];
  }

  // declare type that must be cleaned safely (with a lock file during cleaning)
  $safe_types = array('config', 'i18n');

  // finder to remove all files in a cache directory
  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn', '.sf');

  // finder to find directories (1 level) in a directory
  $dir_finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn', '.sf')->maxdepth(0)->relative();

  // iterate through applications
  $apps = array();
  if ($main_app)
  {
    $apps[] = $main_app;
  }
  else
  {
    $apps = $dir_finder->in($cache_dir);
  }

  foreach ($apps as $app)
  {
    if (!is_dir($cache_dir.'/'.$app)) continue;

    // remove cache for all environments
    foreach ($dir_finder->in($cache_dir.'/'.$app) as $env)
    {
      // which types?
      $types = array();
      if ($main_type)
      {
        $types[] = $main_type;
      }
      else
      {
        $types = $dir_finder->in($cache_dir.'/'.$app.'/'.$env);
      }

      foreach ($types as $type)
      {
        $sub_dir = $cache_dir.'/'.$app.'/'.$env.'/'.$type;

        if (!is_dir($sub_dir)) continue;

        // remove cache files
        if (in_array($type, $safe_types))
        {
          $lock_name = $app.'_'.$env;
          _safe_cache_remove($finder, $sub_dir, $lock_name);
        }
        else
        {
          pake_remove($finder, getcwd().'/'.$sub_dir);
        }
      }
    }
  }
}

function _safe_cache_remove($finder, $sub_dir, $lock_name)
{
  // create a lock file
  pake_touch(getcwd().'/'.$lock_name.'.lck', '');

  // remove cache files
  pake_remove($finder, getcwd().'/'.$sub_dir);

  // release lock
  pake_remove(getcwd().'/'.$lock_name.'.lck', '');
}

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
  pake_mirror($finder, PAKEFILE_DATA_DIR.'/symfony/skeleton/project', getcwd());

  $finder = pakeFinder::type('file')->name('properties.ini', 'apache.conf', 'propel.ini');
  pake_replace_tokens($finder, getcwd(), '##', '##', array('PROJECT_NAME' => $project_name));

  $finder = pakeFinder::type('file')->name('propel.ini');
  pake_replace_tokens($finder, getcwd(), '##', '##', array('PROJECT_DIR' => getcwd()));

  // create symlink if needed
  if (PAKEFILE_SYMLINK)
  {
    pake_symlink(PAKEFILE_LIB_DIR,  getcwd().'/lib/symfony');
    pake_symlink(PAKEFILE_DATA_DIR, getcwd().'/data/symfony');
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
  pake_mirror($finder, PAKEFILE_DATA_DIR.'/symfony/skeleton/app/app', getcwd().'/'.$app);

  // create $app.php or index.php if it is our first app
  $index_name = (!file_exists(getcwd().'/web/index.php') ? 'index' : $app);

  pake_copy(PAKEFILE_DATA_DIR.'/symfony/skeleton/app/web/index.php', getcwd().'/web/'.$index_name.'.php');
  pake_copy(PAKEFILE_DATA_DIR.'/symfony/skeleton/app/web/index_dev.php', getcwd().'/web/'.$app.'_dev.php');

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
  pake_mirror($finder, PAKEFILE_DATA_DIR.'/symfony/skeleton/module/module/', getcwd().'/'.$app.'/modules/'.$module);

  // create basic test
  pake_copy(PAKEFILE_DATA_DIR.'/symfony/skeleton/module/test/actionsTest.php', getcwd().'/test/'.$app.'/'.$module.'ActionsTest.php');

  // customize test file
  pake_replace_tokens($module.'ActionsTest.php', getcwd().'/test/'.$app, '##', '##', $constants);

  // customize php and yml files
  $finder = pakeFinder::type('file')->name('*.php', '*.yml');
  pake_replace_tokens($finder, getcwd().'/'.$app.'/modules/'.$module, '##', '##', $constants);
}

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