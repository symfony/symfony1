<?php

pake_desc('upgrade to a new symfony release');
pake_task('upgrade', 'project_exists');

pake_desc('downgrade to a previous symfony release');
pake_task('downgrade', 'project_exists');

function run_upgrade($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the upgrade script to use (0.6 to upgrade to 0.6 for example).');
  }

  $version = $args[0];

  if ($version == '0.6')
  {
    run_upgrade_to_0_6($task, $args);
  }
  else
  {
    throw new Exception('I have no upgrade script for this release.');
  }
}

function run_upgrade_to_0_6($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  // find all applications for this project
  $apps = pakeFinder::type('directory')->name('modules')->mindepth(1)->maxdepth(1)->relative()->in('apps');

  foreach ($apps as $app_module_dir)
  {
    $app = str_replace('/modules', '', $app_module_dir);
    if ($verbose) echo '>> app       '.pakeApp::excerpt('converting "'.$app.'"'.' application')."\n";

    $app_dir = 'apps/'.$app;

    // upgrade config.php script file
    if ($verbose) echo '>> app       '.pakeApp::excerpt('upgrading config.php')."\n";
    pake_copy(sfConfig::get('sf_symfony_data_dir').'/symfony/skeleton/app/app/config/config.php', $app_dir.'/config/config.php', array('override' => true));

    // change all constants to use sfConfig object
    _upgrade_0_6_constants(array($app_dir.'/modules', $app_dir.'/templates', $app_dir.'/lib'));

    // change view shortcuts in global and modules template directories
    $template_dirs   = pakeFinder::type('directory')->name('templates')->mindepth(1)->maxdepth(1)->in($app_dir.'/modules');
    $template_dirs[] = $app_dir.'/templates';

    _upgrade_0_6_view_shortcuts($template_dirs);
    _upgrade_0_6_mail_to($template_dirs);

    // change comment character in YML files
    _upgrade_0_6_yml_comments($app_dir.'/config');

    // change standard_helpers and i18n format
    _upgrade_0_6_settings($app_dir);

    // move i18n messages XML file
    _upgrade_0_6_i18n($app_dir);

    // rename sfPager to sfPropelPager
    _upgrade_0_6_sfpager($app_dir);

    // change disable_web_debug usage
    _upgrade_0_6_disable_web_debug($app_dir);

    // rename deprecated methods in actions
    $action_dirs = pakeFinder::type('directory')->name('actions')->mindepth(1)->maxdepth(1)->in($app_dir.'/modules');

    _upgrade_0_6_action($action_dirs);
  }

  // constants in global libraries
  _upgrade_0_6_constants('lib');

  // sfPager in global libraries
  _upgrade_0_6_sfpager('lib');

  // clear cache
  run_clear_cache($task, array());
}

function _upgrade_0_6_yml_comments($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $yml_files = pakeFinder::type('file')->name('*.yml')->in($dir);

  $regex = '/^;/m';

  foreach ($yml_files as $yml_file)
  {
    $content = file_get_contents($yml_file);

    if (!preg_match($regex, $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('change YML comment character for "'.$yml_file.'"')."\n";

    $content = preg_replace($regex, '#', $content);

    file_put_contents($yml_file, $content);
  }
}

function _upgrade_0_6_action($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dir);

  $regex = '(forward(404)_(if|unless))';

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('rename deprecated forward methods for "'.$php_file.'"')."\n";

    $content = preg_replace('/'.$regex.'/e', "'forward'.'\\2'.ucfirst('\\3')", $content);

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_6_sfpager($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dir);

  $regex = '(sfPager)';

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('rename sfPager for "'.$php_file.'"')."\n";

    $content = preg_replace('/'.$regex.'/', 'sfPropelPager', $content);

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_6_disable_web_debug($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dir);

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/disable_web_debug/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('converting disable_web_debug for "'.$php_file.'"')."\n";

    $content = preg_replace("#^(\s*).this\->getRequest\(\)\->setAttribute\s*\(\s*'disable_web_debug'\s*,\s*(true|1)\s*,\s*'debug/web'\s*\)\s*;#mi", '\\1sfConfig::set(\'sf_web_debug\', false);', $content);

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_6_i18n($app)
{
  if (is_dir($app.'/i18n/global'))
  {
    // subversion?
    if (is_dir($app.'/i18n/global/.svn'))
    {
      try
      {
        pake_sh('svn move '.$app.'/i18n/global/* '.$app.'/i18n/');
        pake_sh('svn remove '.$app.'/i18n/global');
      }
      catch (Exception $e)
      {
      }
    }
    else
    {
      $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
      pake_mirror($finder, $app.'/i18n/global', $app.'/i18n');
      pake_remove($finder, $app.'/i18n/global');
      pake_remove($app.'/i18n/global', '');
    }
  }
}

function _upgrade_0_6_settings($app)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $content = file_get_contents($app.'/config/settings.yml');

  if ($verbose) echo '>> file      '.pakeApp::excerpt('converting settings.yml')."\n";

  if (!preg_match('/(standard_helpers|standard_helpers):\s*\[/', $content))
  {
    $content = preg_replace('/^([;\s]+)(helper_standard|standard_helpers)\:(\s+)(.+)$/me', "'$1standard_helpers:$3['.implode(', ', explode(',', '$4')).']'", $content);
  }

  // i18n
  $content = preg_replace('/^([;\s]+)is_i18n\:(\s+)(.+)$/m', '$1i18n:   $2$3', $content);

  $default_culture = 'en';
  if (preg_match('/^.+default_culture\:\s*(.+)$/m', $content, $match))
  {
    $default_culture = $match[1];
    $content = preg_replace('/^.+default_culture\:\s*.+$/m', '', $content);

    // create the new i18n configuration file
    if (!is_readable($app.'/config/i18n.yml'))
    {
      if ($verbose) echo '>> file+     '.pakeApp::excerpt('new i18n.yml configuration file')."\n";
      $i18n = "all:\n  default_culture: $default_culture\n";
      file_put_contents($app.'/config/i18n.yml', $i18n);
    }
  }

  file_put_contents($app.'/config/settings.yml', $content);
}

function _upgrade_0_6_view_shortcuts($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = '(context\-|params\[|request\-|user\-|view\-|last_module|last_action|first_module|first_action)';

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/\$'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('converting view shortcuts for "'.$php_file.'"')."\n";

    $content = preg_replace('/\$'.$regex.'/', '$sf_\\1', $content);

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_6_mail_to($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = 'mail_to\s*\(\s*([^,]+?), ([^,]+?)\)';

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('converting mail_to for "'.$php_file.'"')."\n";

    $content = preg_replace('/'.$regex.'/', 'mail_to($1, $1, \'encode=$2\')', $content);

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_6_constants($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $php_files = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = '((SF|APP|MOD)_[A-Z0-9_]+)';

  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose) echo '>> file      '.pakeApp::excerpt('converting constants for "'.$php_file.'"')."\n";

    $content = preg_replace('/defined\(\''.$regex.'\'\)/e', "'sfConfig::get(\''.strtolower('\\1').'\')'", $content);
    $content = preg_replace('/define\(\''.$regex.'\',\s*(.+?)\)/e', "'sfConfig::set(\''.strtolower('\\1').'\', \\3)'", $content);
    $content = preg_replace('/'.$regex.'/e', "'sfConfig::get(\''.strtolower('\\1').'\')'", $content);

    file_put_contents($php_file, $content);
  }
}

?>