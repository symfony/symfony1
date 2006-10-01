<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pake_desc('launch unit tests');
pake_task('test-unit', 'project_exists');

pake_desc('launch functional tests for an application');
pake_task('test-functional', 'project_exists');

pake_desc('launch all tests');
pake_task('test-all', 'project_exists');

function run_test_all($task, $args)
{
  require_once(sfConfig::get('sf_symfony_lib_dir').'/vendor/lime/lime.php');

  $h = new lime_harness(new lime_output_color());
  $h->base_dir = sfConfig::get('sf_test_dir');

  // register all tests
  $finder = pakeFinder::type('file')->ignore_version_control()->name('*Test.php');
  $h->register($finder->in($h->base_dir));

  $h->run();
}

function run_test_functional($task, $args)
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

  if (isset($args[1]))
  {
    $test = $args[1];
    $test_file = sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$test.'Test.php';

    if (!is_readable($test_file))
    {
      throw new Exception(sprintf('The functionnal test "%s" does not exist', $test));
    }

    include($test_file);
  }
  else
  {
    require_once(sfConfig::get('sf_symfony_lib_dir').'/vendor/lime/lime.php');

    $h = new lime_harness(new lime_output_color());
    $h->base_dir = sfConfig::get('sf_test_dir').'/functional/'.$app;

    // register functional tests
    $finder = pakeFinder::type('file')->ignore_version_control()->name('*Test.php');
    $h->register($finder->in($h->base_dir));

    $h->run();
  }
}

function run_test_unit($task, $args)
{
  if (isset($args[0]))
  {
    foreach ($args as $path)
    {
      $files = pakeFinder::type('file')->ignore_version_control()->name($path.'Test.php')->in(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'unit');
      foreach ($files as $file)
      {
        if (!is_readable($file))
        {
          throw new Exception(sprintf('The functionnal test "%s" does not exist', $path));
        }

        include($file);
      }
    }
  }
  else
  {
    require_once(sfConfig::get('sf_symfony_lib_dir').'/vendor/lime/lime.php');

    $h = new lime_harness(new lime_output_color());
    $h->base_dir = sfConfig::get('sf_test_dir').'/unit';

    // register functional tests
    $finder = pakeFinder::type('file')->ignore_version_control()->name('*Test.php');
    $h->register($finder->in($h->base_dir));

    $h->run();
  }
}
