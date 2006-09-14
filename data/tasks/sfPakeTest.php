<?php

pake_desc('launch project test suite');
pake_task('test');

pake_desc('launch a test');
pake_task('onetest');

pake_desc('launch test suite for a plugin');
pake_task('plugin-test');

function run_onetest($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the app to test.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception(sprintf('The app "%s" does not exist.', $app));
  }

  if (!isset($args[1]))
  {
    throw new Exception('You must provide a test file.');
  }

  $test_file = $args[1];

  // define constants
  define('SF_ROOT_DIR',    getcwd());
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', 'test');
  define('SF_DEBUG',       true);

  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

  include(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$test_file.'Test.php');
}
function run_test($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the app to test.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception(sprintf('The app "%s" does not exist.', $app));
  }

  // define constants
  define('SF_ROOT_DIR',    getcwd());
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', 'test');
  define('SF_DEBUG',       true);

  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

  require_once(sfConfig::get('sf_symfony_lib_dir').'/vendor/lime/lime.php');

  $h = new lime_harness(new lime_output_color());

  $h->base_dir = sfConfig::get('sf_test_dir');

  // unit tests
  $finder = pakeFinder::type('file')->name('*Test.php');
  if (is_dir('test/project'))
  {
    $h->register($finder->in($h->base_dir.'/project'));
  }
  $h->register($finder->in($h->base_dir.'/'.$app));

  $h->run();
}

function run_plugin_test($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the plugin to test.');
  }

  $plugin = $args[0];

  $dir = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin;
  if (!is_dir($dir))
  {
    throw new Exception(sprintf('The plugin "%s" does not exist.', $plugin));
  }

  pake_import('simpletest', false);
  pakeSimpletestTask::call_simpletest($task, 'text', array($dir));
}
