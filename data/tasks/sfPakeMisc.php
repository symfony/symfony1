<?php

pake_desc('clear cached information');
pake_task('clear-cache', 'project_exists');
pake_alias('cc', 'clear-cache');

pake_desc('fix directories permissions');
pake_task('fix-perms', 'project_exists');

function run_fix_perms($task, $args)
{
  $sf_root_dir = sfConfig::get('sf_root_dir');

  pake_chmod(sfConfig::get('sf_cache_dir_name'), $sf_root_dir, 0777);
  pake_chmod(sfConfig::get('sf_log_dir_name'), $sf_root_dir, 0777);

  $dirs = array('cache', 'upload', 'log');
  $dir_finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn');
  $file_finder = pakeFinder::type('file')->prune('.svn')->discard('.svn');
  foreach ($dirs as $dir)
  {
    pake_chmod($dir_finder, sfConfig::get('sf_'.$dir.'_dir'), 0777);
    pake_chmod($file_finder, sfConfig::get('sf_'.$dir.'_dir'), 0666);
  }
}

function run_clear_cache($task, $args)
{
  if (!file_exists('cache'))
  {
    throw new Exception('Cache directory does not exist.');
  }

  $cache_dir = sfConfig::get('sf_cache_dir_name');

  // app
  $main_app = '';
  if (isset($args[0]))
  {
    $main_app = $args[0];
  }

  // type (template, i18n or config)
  $main_type = '';
  if (isset($args[1]))
  {
    $main_type = $args[1];
  }

  // declare type that must be cleaned safely (with a lock file during cleaning)
  $safe_types = array(sfConfig::get('sf_app_config_dir_name'), sfConfig::get('sf_app_i18n_dir_name'));

  // finder to remove all files in a cache directory
  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn', '.sf');

  // finder to find directories (1 level) in a directory
  $dir_finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn', '.sf')->maxdepth(0)->relative();

  // iterate through applications
  $apps = array();
  if ($main_app)
  {
    $apps[] = $main_app;
  }
  else
  {
    $apps = $dir_finder->in($cache_dir);
  }

  foreach ($apps as $app)
  {
    if (!is_dir($cache_dir.'/'.$app))
    {
      continue;
    }

    // remove cache for all environments
    foreach ($dir_finder->in($cache_dir.'/'.$app) as $env)
    {
      // which types?
      $types = array();
      if ($main_type)
      {
        $types[] = $main_type;
      }
      else
      {
        $types = $dir_finder->in($cache_dir.'/'.$app.'/'.$env);
      }

      $sf_root_dir = sfConfig::get('sf_root_dir');
      foreach ($types as $type)
      {
        $sub_dir = $cache_dir.'/'.$app.'/'.$env.'/'.$type;

        if (!is_dir($sub_dir))
        {
          continue;
        }

        // remove cache files
        if (in_array($type, $safe_types))
        {
          $lock_name = $app.'_'.$env;
          _safe_cache_remove($finder, $sub_dir, $lock_name);
        }
        else
        {
          pake_remove($finder, $sf_root_dir.'/'.$sub_dir);
        }
      }
    }
  }
}

function _safe_cache_remove($finder, $sub_dir, $lock_name)
{
  $sf_root_dir = sfConfig::get('sf_root_dir');

  // create a lock file
  pake_touch($sf_root_dir.'/'.$lock_name.'.lck', '');

  // change mode so the web user can remove it if we die
  pake_chmod($lock_name.'.lck', $sf_root_dir, 0777);

  // remove cache files
  pake_remove($finder, $sf_root_dir.'/'.$sub_dir);

  // release lock
  pake_remove($sf_root_dir.'/'.$lock_name.'.lck', '');
}
