<?php

pake_desc('create classes for current model');
pake_task('build-propelmodel', 'project_exists');
pake_task('build-model');

pake_desc('create sql for current model');
pake_task('build-propelsql', 'project_exists');
pake_task('build-sql');

pake_desc('create schema.xml from existing database');
pake_task('build-propelschema', 'project_exists');
pake_task('build-schema');

pake_desc('create database for current model');
pake_task('build-propeldb', 'project_exists');
pake_task('build-db');

pake_desc('insert sql for current model');
pake_task('insert-propelsql', 'project_exists');
pake_task('insert-sql');

function run_build_model($task, $args)
{
  throw new Exception('This task is deprecated. Please use "build-propelmodel".');
}

function run_build_sql($task, $args)
{
  throw new Exception('This task is deprecated. Please use "build-propelsql".');
}

function run_build_schema($task, $args)
{
  throw new Exception('This task is deprecated. Please use "build-propelschema".');
}

function run_build_db($task, $args)
{
  throw new Exception('This task is deprecated. Please use "build-propeldb".');
}

function run_insert_sql($task, $args)
{
  throw new Exception('This task is deprecated. Please use "insert-propelsql".');
}

function run_build_propelmodel($task, $args)
{
  _call_phing($task, 'build-om');
}

function run_build_propelsql($task, $args)
{
  _call_phing($task, 'build-sql');
}

function run_build_propeldb($task, $args)
{
  _call_phing($task, 'build-db');
}

function run_insert_propelsql($task, $args)
{
  _call_phing($task, 'insert-sql');
}

function run_build_propelschema($task, $args)
{
  _call_phing($task, 'build-model-schema', false);

  // fix database name
  if (file_exists('config/schema.xml'))
  {
    $schema = file_get_contents('config/schema.xml');
    $schema = preg_replace('/<database\s+name="[^"]+"/s', '<database name="symfony"', $schema);
    file_put_contents('config/schema.xml', $schema);
  }
}

function _call_phing($task, $task_name, $check_schema = true)
{
  if ($check_schema && !glob('config/*schema.xml'))
  {
    throw new Exception('You must create a schema.xml file.');
  }

  // update propel root dir in propel.ini
  $propelIni = file_get_contents(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini');
  $propelIni = preg_replace('/^\s*propel.output.dir\s*=\s*.+?$/m', 'propel.output.dir = '.sfConfig::get('sf_root_dir'), $propelIni);
  file_put_contents(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini', $propelIni);

  $propel_generator_dir = sfConfig::get('sf_symfony_lib_dir').'/vendor/propel-generator';

  // call phing targets
  pake_import('Phing', false);
  pakePhingTask::call_phing($task, array($task_name), sfConfig::get('sf_symfony_data_dir').'/bin/build.xml', array('project' => $task->get_property('name', 'symfony'), 'lib_dir' => sfConfig::get('sf_symfony_lib_dir'), 'data_dir' => sfConfig::get('sf_symfony_data_dir'), 'propel_generator_dir' => $propel_generator_dir));

  chdir(sfConfig::get('sf_root_dir'));
}

?>