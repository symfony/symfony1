<?php

pake_desc('create classes for current model');
pake_task('build-model', 'project_exists');

pake_desc('create sql for current model');
pake_task('build-sql', 'project_exists');

pake_desc('create schema.xml from existing database');
pake_task('build-schema', 'project_exists');

pake_desc('create database for current model');
pake_task('build-db', 'project_exists');

pake_desc('insert sql for current model');
pake_task('insert-sql', 'project_exists');

function run_build_model($task, $args)
{
  _call_phing($task, 'build-om');
}

function run_build_sql($task, $args)
{
  _call_phing($task, 'build-sql');
}

function run_build_db($task, $args)
{
  _call_phing($task, 'build-db');
}

function run_insert_sql($task, $args)
{
  _call_phing($task, 'insert-sql');
}

function run_build_schema($task, $args)
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
  if ($check_schema && !file_exists('config/schema.xml'))
  {
    throw new Exception('You must create a schema.xml file.');
  }

  // FIXME: we update propel.ini with uptodate values

  $propel_generator_dir = sfConfig::get('sf_symfony_lib_dir').'/propel-generator';

  $current_dir = getcwd();

  // call phing targets
  pake_import('Phing', false);
  pakePhingTask::call_phing($task, array($task_name), dirname(__FILE__).'/../bin/build.xml', array('project' => $task->get_property('name', 'symfony'), 'lib_dir' => sfConfig::get('sf_symfony_lib_dir'), 'data_dir' => sfConfig::get('sf_symfony_data_dir'), 'propel_generator_dir' => $propel_generator_dir));

  chdir($current_dir);
}

?>