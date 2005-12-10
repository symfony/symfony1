<?php

pake_desc('create classes for current model');
pake_task('build-model', 'project_exists');

pake_desc('create sql for current model');
pake_task('build-sql', 'project_exists');

pake_desc('create schema.xml from existing database');
pake_task('build-schema', 'project_exists');

function run_build_model($task, $args)
{
  _call_phing($task, 'build-om');
}

function run_build_sql($task, $args)
{
  _call_phing($task, 'build-sql');
}

function run_build_schema($task, $args)
{
  _call_phing($task, 'build-model-schema', false);

  // fix database name
//  $schema = file_get_contents('config/schema.xml');
//  $schema = preg_replace('//', '', $schema);
//  file_put_contents($schema, 'config/schema.xml');
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

?>