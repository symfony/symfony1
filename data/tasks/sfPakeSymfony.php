<?php

pake_desc('freeze symfony libraries');
pake_task('freeze', 'project_exists');

pake_desc('unfreeze symfony libraries');
pake_task('unfreeze', 'project_exists');

function run_freeze($task, $args)
{
  // check that the symfony librairies are not already freeze for this project
  if (is_readable('lib/symfony') && !is_link('lib/symfony'))
  {
    throw new Exception('You can only freeze when lib/symfony is empty (symfony as PEAR) or if lib/symfony is a symlink to a symfony installation.');
  }

  if (is_readable('data/symfony') && !is_link('data/symfony'))
  {
    throw new Exception('You can only freeze when data/symfony is empty (symfony as PEAR) or if data/symfony is a symlink to a symfony installation.');
  }

  if (is_link('lib/symfony'))
  {
    $symfony_lib_dir = realpath(getcwd().'/lib/symfony');
    pake_remove('lib/symfony', '');
  }
  else
  {
    $symfony_lib_dir = PAKEFILE_LIB_DIR;
  }

  if (is_link('data/symfony'))
  {
    $symfony_data_dir = realpath(getcwd().'/data/symfony');
    pake_remove('data/symfony', '');
  }
  else
  {
    $symfony_data_dir = PAKEFILE_DATA_DIR;
  }

  $verbose = pakeApp::get_instance()->get_verbose();
  if ($verbose) echo '>> freeze    '.pakeApp::excerpt('freezing lib found in "'.$symfony_lib_dir.'"')."\n";
  if ($verbose) echo '>> freeze    '.pakeApp::excerpt('freezing data found in "'.$symfony_data_dir.'"')."\n";

  $finder = pakeFinder::type('any')->ignore_version_control();
  pake_mirror($finder, $symfony_lib_dir, 'lib/symfony');
  pake_mirror($finder, $symfony_data_dir, 'data/symfony');

  // install the command line
  pake_copy('data/symfony/bin/symfony.php', 'symfony.php');
}

function run_unfreeze($task, $args)
{
  // remove lib/symfony and data/symfony directories
  if (is_link('lib/symfony'))
  {
    throw new Exception('You can unfreeze only if you froze the symfony libraries before.');
  }

  if (!is_dir('lib/symfony') || !is_readable('lib/symfony'))
  {
    throw new Exception('Your lib/symfony directory does not seem to be accessible.');
  }

  $finder = pakeFinder::type('any');
  pake_remove($finder, 'lib/symfony');
  pake_remove('lib/symfony', '');
  pake_remove($finder, 'data/symfony');
  pake_remove('data/symfony', '');
  pake_remove('symfony.php', '');
}
