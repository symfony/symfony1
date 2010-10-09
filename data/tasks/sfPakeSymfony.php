<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pake_desc('freeze symfony libraries');
pake_task('freeze', 'project_exists');

pake_desc('unfreeze symfony libraries');
pake_task('unfreeze', 'project_exists');

function run_freeze($task, $args)
{
  // check that the symfony librairies are not already freeze for this project
  if (is_readable(sfConfig::get('sf_lib_dir').'/symfony'))
  {
    throw new Exception('You can only freeze when lib/symfony is empty.');
  }

  if (is_readable(sfConfig::get('sf_data_dir').'/symfony'))
  {
    throw new Exception('You can only freeze when data/symfony is empty.');
  }

  if (is_readable(sfConfig::get('sf_web_dir').'/sf'))
  {
    throw new Exception('You can only freeze when web/sf is empty.');
  }

  if (is_link(sfConfig::get('sf_web_dir').'/sf'))
  {
    pake_remove(sfConfig::get('sf_web_dir').'/sf', '');
  }

  $symfony_lib_dir  = sfConfig::get('sf_symfony_lib_dir');
  $symfony_data_dir = sfConfig::get('sf_symfony_data_dir');

  pake_echo_action('freeze', 'freezing lib found in "'.$symfony_lib_dir.'"');
  pake_echo_action('freeze', 'freezing data found in "'.$symfony_data_dir.'"');

  pake_mkdirs(sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'symfony');
  pake_mkdirs(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'symfony');

  $finder = pakeFinder::type('any')->ignore_version_control();
  pake_mirror($finder, $symfony_lib_dir, sfConfig::get('sf_lib_dir').'/symfony');
  pake_mirror($finder, $symfony_data_dir, sfConfig::get('sf_data_dir').'/symfony');

  pake_rename(sfConfig::get('sf_data_dir').'/symfony/web/sf', sfConfig::get('sf_web_dir').'/sf');

  // change symfony paths in config/config.php
  file_put_contents('config/config.php.bak', "$symfony_lib_dir#$symfony_data_dir");
  _change_symfony_dirs("dirname(__FILE__).'/../lib/symfony'", "dirname(__FILE__).'/../data/symfony'");

  // install the command line
  pake_copy($symfony_data_dir.'/bin/symfony.php', 'symfony.php');
}

function run_unfreeze($task, $args)
{
  // remove lib/symfony and data/symfony directories
  if (!is_dir('lib/symfony'))
  {
    throw new Exception('You can unfreeze only if you froze the symfony libraries before.');
  }

  $dirs = explode('#', file_get_contents('config/config.php.bak'));
  _change_symfony_dirs('\''.$dirs[0].'\'', '\''.$dirs[1].'\'');

  $finder = pakeFinder::type('any')->ignore_version_control();
  pake_remove($finder, sfConfig::get('sf_lib_dir').'/symfony');
  pake_remove(sfConfig::get('sf_lib_dir').'/symfony', '');
  pake_remove($finder, sfConfig::get('sf_data_dir').'/symfony');
  pake_remove(sfConfig::get('sf_data_dir').'/symfony', '');
  pake_remove('symfony.php', '');
  pake_remove($finder, sfConfig::get('sf_web_dir').'/sf');
  pake_remove(sfConfig::get('sf_web_dir').'/sf', '');
}

function _change_symfony_dirs($symfony_lib_dir, $symfony_data_dir)
{
  $content = file_get_contents('config/config.php');
  $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
  $content = preg_replace("/^(\s*.sf_symfony_data_dir\s*=\s*).+?;/m", "$1$symfony_data_dir;", $content);
  file_put_contents('config/config.php', $content);
}
