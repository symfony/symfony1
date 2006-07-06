<?php

pake_desc('create classes for current model');
pake_task('propel-build-model', 'project_exists');
pake_task('build-model');

pake_desc('create sql for current model');
pake_task('propel-build-sql', 'project_exists');
pake_task('build-sql');

pake_desc('create schema.xml from existing database');
pake_task('propel-build-schema', 'project_exists');
pake_task('build-schema');

pake_desc('create schema.xml from schema.yml');
pake_task('propel-convert-yml-schema', 'project_exists');

pake_desc('create database for current model');
pake_task('propel-build-db', 'project_exists');
pake_task('build-db');

pake_desc('insert sql for current model');
pake_task('propel-insert-sql', 'project_exists');
pake_task('insert-sql');

pake_desc('generate propel model and sql and initialize database');
pake_task('propel-build-all', 'project_exists', 'propel-build-model', 'propel-build-sql', 'propel-insert-sql');

function run_build_model($task, $args)
{
  throw new Exception('This task is deprecated. Please use "propel-build-model".');
}

function run_build_sql($task, $args)
{
  throw new Exception('This task is deprecated. Please use "propel-build-sql".');
}

function run_build_schema($task, $args)
{
  throw new Exception('This task is deprecated. Please use "propel-build-schema".');
}

function run_build_db($task, $args)
{
  throw new Exception('This task is deprecated. Please use "propel-build-db".');
}

function run_insert_sql($task, $args)
{
  throw new Exception('This task is deprecated. Please use "propel-insert-sql".');
}

function run_propel_convert_yml_schema($task, $args)
{
  _propel_convert_yml_schema(true);
}

function _propel_convert_yml_schema($check_schema = true, $prefix = '')
{
  $schemas = pakeFinder::type('file')->name('*schema.yml')->relative()->in('config');
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

  $verbose = pakeApp::get_instance()->get_verbose();
  $db_schema = new sfPropelDatabaseSchema();
  foreach ($schemas as $schema)
  {
    $db_schema->loadYAML('config/'.$schema);

    if ($verbose) echo '>> schema    '.pakeApp::excerpt('converting "'.$schema.'"'.' to XML')."\n";

    file_put_contents('config/'.$prefix.str_replace('.yml', '.xml', $schema), $db_schema->asXML());
  }
}

function run_build_all($task, $args)
{
}

function run_propel_build_model($task, $args)
{
  _propel_convert_yml_schema(false, 'generated-');
  _call_phing($task, 'build-om');
  $finder = pakeFinder::type('file')->name('generated-*schema.xml');
  pake_remove($finder, 'config');
}

function run_propel_build_sql($task, $args)
{
  _propel_convert_yml_schema(false, 'generated-');
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
  _call_phing($task, 'insert-sql');
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
}

function _call_phing($task, $task_name, $check_schema = true)
{
  $schemas = pakeFinder::type('file')->name('*schema.xml')->relative()->in('config');
  if ($check_schema && !$schemas)
  {
    throw new Exception('You must create a schema.xml file.');
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
  pakePhingTask::call_phing($task, array($task_name), sfConfig::get('sf_symfony_data_dir').'/bin/build.xml', $options);

  pake_remove($propelIniFileName, '');

  chdir(sfConfig::get('sf_root_dir'));
}
