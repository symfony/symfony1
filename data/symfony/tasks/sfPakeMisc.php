<?php

pake_desc('clear cached information');
pake_task('clear-cache', 'project_exists');
pake_alias('cc', 'clear-cache');

pake_desc('fix directories permissions');
pake_task('fix-perms', 'project_exists');

function run_fix_perms($task, $args)
{
  $finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn');
  pake_chmod($finder, getcwd().'/web/uploads', 0777);
  pake_chmod('log', getcwd(), 0777);
  pake_chmod('cache', getcwd(), 0777);

  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn');
  pake_chmod($finder, getcwd().'/web/uploads', 0666);
  pake_chmod($finder, getcwd().'/log', 0666);
}

function run_clear_cache($task, $args)
{
  if (!file_exists('cache'))
  {
    throw new Exception('Cache directory does not exist.');
  }

  $cache_dir = 'cache';

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
  $safe_types = array('config', 'i18n');

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
    if (!is_dir($cache_dir.'/'.$app)) continue;

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

      foreach ($types as $type)
      {
        $sub_dir = $cache_dir.'/'.$app.'/'.$env.'/'.$type;

        if (!is_dir($sub_dir)) continue;

        // remove cache files
        if (in_array($type, $safe_types))
        {
          $lock_name = $app.'_'.$env;
          _safe_cache_remove($finder, $sub_dir, $lock_name);
        }
        else
        {
          pake_remove($finder, getcwd().'/'.$sub_dir);
        }
      }
    }
  }
}

function _safe_cache_remove($finder, $sub_dir, $lock_name)
{
  // create a lock file
  pake_touch(getcwd().'/'.$lock_name.'.lck', '');

  // change mode so the web user can remove it if we die
  pake_chmod($lock_name.'.lck', getcwd(), 0777);

  // remove cache files
  pake_remove($finder, getcwd().'/'.$sub_dir);

  // release lock
  pake_remove(getcwd().'/'.$lock_name.'.lck', '');
}

?>