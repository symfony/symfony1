<?php

pake_desc('launch project test suite');
pake_task('test');

function run_test($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the app to test.');
  }

  $app = $args[0];

  if (!is_dir($app))
  {
    throw new Exception('You must provide the app to test.');
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

?>