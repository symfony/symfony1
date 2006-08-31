<?php

pake_desc('create classes for current model');
pake_task('propel-build-model', 'project_exists');

pake_desc('create sql for current model');
pake_task('propel-build-sql', 'project_exists');

pake_desc('create schema.xml from existing database');
pake_task('propel-build-schema', 'project_exists');

pake_desc('create schema.xml from schema.yml');
pake_task('propel-convert-yml-schema', 'project_exists');

pake_desc('create schema.yml from schema.xml');
pake_task('propel-convert-xml-schema', 'project_exists');

pake_desc('load data from fixtures directory');
pake_task('propel-load-data', 'project_exists');

pake_desc('create database for current model');
pake_task('propel-build-db', 'project_exists');

pake_desc('insert sql for current model');
pake_task('propel-insert-sql', 'project_exists');

pake_desc('generate propel model and sql and initialize database');
pake_task('propel-build-all', 'project_exists');

pake_desc('generate propel model and sql and initialize database, and load data');
pake_task('propel-build-all-load', 'propel-build-all');

function run_propel_convert_yml_schema($task, $args)
{
  _propel_convert_yml_schema(true);
}

function run_propel_convert_xml_schema($task, $args)
{
  _propel_convert_xml_schema(true);
}

function _propel_convert_yml_schema($check_schema = true, $prefix = '')
{
  $finder = pakeFinder::type('file')->name('*schema.yml');
  $schemas = array_merge($finder->in('config'), $finder->in('data/plugins'));
  if ($check_schema && !count($schemas))
  {
    throw new Exception('You must create a schema.yml file.');
  }

  $sf_symfony_lib_dir = sfConfig::get('sf_symfony_lib_dir');
  require_once($sf_symfony_lib_dir.'/util/Spyc.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfYaml.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfToolkit.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfInflector.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfException.class.php');
  require_once($sf_symfony_lib_dir.'/addon/propel/sfPropelDatabaseSchema.class.php');

  $db_schema = new sfPropelDatabaseSchema();
  foreach ($schemas as $schema)
  {
    $db_schema->loadYAML($schema);

    pake_echo_action('schema', 'converting "'.$schema.'"'.' to XML');

    file_put_contents('config'.DIRECTORY_SEPARATOR.$prefix.str_replace('.yml', '.xml', basename($schema)), $db_schema->asXML());
  }
}

function _propel_convert_xml_schema($check_schema = true, $prefix = '')
{
  $finder = pakeFinder::type('file')->name('*schema.xml');
  $schemas = array_merge($finder->in('config'), $finder->in('data/plugins'));
  if ($check_schema && !count($schemas))
  {
    throw new Exception('You must create a schema.xml file.');
  }

  $sf_symfony_lib_dir = sfConfig::get('sf_symfony_lib_dir');
  require_once($sf_symfony_lib_dir.'/util/Spyc.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfYaml.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfToolkit.class.php');
  require_once($sf_symfony_lib_dir.'/util/sfInflector.class.php');
  require_once($sf_symfony_lib_dir.'/exception/sfException.class.php');
  require_once($sf_symfony_lib_dir.'/addon/propel/sfPropelDatabaseSchema.class.php');

  $db_schema = new sfPropelDatabaseSchema();
  foreach ($schemas as $schema)
  {
    $db_schema->loadXML($schema);

    pake_echo_action('schema', 'converting "'.$schema.'"'.' to YAML');

    file_put_contents('config'.DIRECTORY_SEPARATOR.$prefix.str_replace('.xml', '.yml', basename($schema)), $db_schema->asYAML());
  }
}

function _propel_copy_xml_schema_from_plugins($prefix = '')
{
  $finder = pakeFinder::type('file')->name('*schema.xml');
  $schemas = $finder->in('data/plugins');

  foreach ($schemas as $schema)
  {
    pake_copy($schema, 'config'.DIRECTORY_SEPARATOR.$prefix.basename($schema));
  }
}

function run_propel_build_all($task, $args)
{
  run_propel_build_model($task, $args);
  run_propel_build_sql($task, $args);
  run_propel_insert_sql($task, $args);
}

function run_propel_build_all_load($task, $args)
{
  run_propel_build_all($task, $args);
  run_propel_load_data($task, $args);
}

function run_propel_build_model($task, $args)
{
  _propel_convert_yml_schema(false, 'generated-');
  _propel_copy_xml_schema_from_plugins('generated-');
  _call_phing($task, 'build-om');
  $finder = pakeFinder::type('file')->name('generated-*schema.xml');
  pake_remove($finder, 'config');
}

function run_propel_build_sql($task, $args)
{
  _propel_convert_yml_schema(false, 'generated-');
  _propel_copy_xml_schema_from_plugins('generated-');
  _call_phing($task, 'build-sql');
  $finder = pakeFinder::type('file')->name('generated-*schema.xml');
  pake_remove($finder, 'config');
}

function run_propel_build_db($task, $args)
{
  _call_phing($task, 'build-db');
}

function run_propel_insert_sql($task, $args)
{
  _propel_convert_yml_schema(false, 'generated-');
  _propel_copy_xml_schema_from_plugins('generated-');
  _call_phing($task, 'insert-sql');
  $finder = pakeFinder::type('file')->name('generated-*schema.xml');
  pake_remove($finder, 'config');
}

function run_propel_build_schema($task, $args)
{
  _call_phing($task, 'build-model-schema', false);

  // fix database name
  if (file_exists('config/schema.xml'))
  {
    $schema = file_get_contents('config/schema.xml');
    $schema = preg_replace('/<database\s+name="[^"]+"/s', '<database name="propel"', $schema);
    file_put_contents('config/schema.xml', $schema);
  }

  if (!isset($args[0]) || $args[0] != 'xml')
  {
    _propel_convert_xml_schema(false, '');
    $finder = pakeFinder::type('file')->name('schema.xml');
    pake_remove($finder, 'config');
  }
}

/**
 * loads yml data from fixtures directory and inserts into database
 *
 * @example symfony load-data frontend
 * @example symfony load-data frontend dev fixtures append
 *
 * @todo replace delete argument with flag -d
 *
 * @param object $task
 * @param array $args
 */
function run_propel_load_data($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the app.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception('The app "'.$app.'" does not exist.');
  }

  $env = empty($args[1]) ? 'dev' : $args[1];

  // define constants
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', $env);
  define('SF_DEBUG',       true);

  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

  if (count($args) > 1 && $args[count($args) - 1] == 'append')
  {
    array_pop($args);
    $delete = false;
  }
  else
  {
    $delete = true;
  }

  if (count($args) == 1)
  {
    $fixtures_dirs = sfFinder::type('dir')->name('fixtures')->relative()->in(sfConfig::get('sf_data_dir'));
  }
  else
  {
    $fixtures_dirs = array_slice($args, 1);
  }

  $databaseManager = new sfDatabaseManager();
  $databaseManager->initialize();

  $data = new sfPropelData();
  $data->setDeleteCurrentData($delete);

  foreach ($fixtures_dirs as $fixtures_dir)
  {
    $fixtures_dir = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.$fixtures_dir;
    if (!is_readable($fixtures_dir))
    {
      throw new Exception(sprintf('Fixture directory "%s" does not exist.', $fixtures_dir));
    }

    $data->loadData($fixtures_dir);
  }
}

function _call_phing($task, $task_name, $check_schema = true)
{
  $schemas = pakeFinder::type('file')->name('*schema.xml')->relative()->in('config');
  if ($check_schema && !$schemas)
  {
    throw new Exception('You must create a schema.yml or schema.xml file.');
  }

  // create a tmp propel.ini configuration file
  $propelIniFileName = tempnam(sfConfig::get('sf_config_dir'), 'propelini');
  pake_copy(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini', $propelIniFileName, array('override' => true));

  // update propel root dir
  $propelIni = file_get_contents($propelIniFileName);
  $propelIni = preg_replace('/^\s*propel.output.dir\s*=\s*.+?$/m', 'propel.output.dir = '.sfConfig::get('sf_root_dir'), $propelIni);
  file_put_contents($propelIniFileName, $propelIni);

  // update database information
  $projectConfigFile = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'databases.yml';
  $appConfigFile     = sfConfig::get('sf_apps_dir_name').DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_config_dir_name').DIRECTORY_SEPARATOR.'databases.yml';

  $propel_generator_dir = sfConfig::get('sf_symfony_lib_dir').'/vendor/propel-generator';

  $options = array(
    'project' => $task->get_property('name', 'symfony'),
    'lib_dir' => sfConfig::get('sf_symfony_lib_dir'),
    'data_dir' => sfConfig::get('sf_symfony_data_dir'),
    'propel_generator_dir' => $propel_generator_dir,
    'propel_ini' => basename($propelIniFileName),
  );

  // call phing targets
  pake_import('Phing', false);
  if (false === strpos('propel-generator', get_include_path()))
  {
    set_include_path(sfConfig::get('sf_symfony_lib_dir').'/vendor/propel-generator/classes'.PATH_SEPARATOR.get_include_path());
  }
  pakePhingTask::call_phing($task, array($task_name), sfConfig::get('sf_symfony_data_dir').'/bin/build.xml', $options);

  pake_remove($propelIniFileName, '');

  chdir(sfConfig::get('sf_root_dir'));
}
