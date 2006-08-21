<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Log manager
 *
 * @package default
 * @author Joe Simms
 **/
class sfLogManager
{
  /** the default meta file name for this log manager */
  const META_FILENAME = 'log.meta';

  /** the default period to rotate logs in days */
  const DEF_PERIOD    = 7;

  /** the default number of log historys to store, one history is created for every period */
  const DEF_HISTORY   = 10;

  /**
   * rotates log file
   *
   * @return void
   * @author Joe Simms
   **/
  public static function rotate($app, $env, $period = null, $history = null, $override = false)
  {
    $logfile = $app.'_'.$env;
    $logdir = sfConfig::get('sf_log_dir');

    $period = isset($period) ? $period : self::DEF_PERIOD;
    $metafile = $logdir.'/'.self::META_FILENAME;
     
    $meta = file_exists($metafile) ? sfIni::load($metafile, true) : array();

    // set history, default to value passed, then to previous value, the to default 
    $meta_history = isset($meta[$logfile]['history']) ? $meta[$logfile]['history'] : self::DEF_HISTORY;
    $history = isset($history) ? $history : $meta_history;

    $today = date('Ymd');
    $rotate_log_on = isset($meta[$logfile]['rotate_on']) ? $meta[$logfile]['rotate_on'] : null;

    $src_log = $logdir.'/'.$logfile.'.log';
    $dest_log = $logdir.'/history/'.$logfile.'_'.$today.'.log';

    // if rotate log on date doesn't exist, or that date is today, then rotate the log
    if(!isset($rotate_log_on) || ($rotate_log_on == $today) || $override)
    {           
      // check history folder exists
      if(!is_dir($logdir.'/history'))
      {
        mkdir($logdir.'/history', 0777);
      }
      // create a lock file
      $lock_name = $app.'_'.$env.'.lck';
      touch(sfConfig::get('sf_root_dir').'/'.$lock_name);

      // if log file exists rotate it
      if(file_exists($src_log))
      {
        // check if the log file has already been rotated today
        if(file_exists($dest_log))
        {
          // append log to existing rotated log
          $handle = fopen($dest_log, 'a');
          $append = file_get_contents($src_log);
          fwrite($handle, $append);
        }
        else
        {
          // copy log
          copy($src_log, $dest_log);          
        }

        // remove the log file
        unlink($src_log);

        // get all log files for this application and environment
        $logs = sfFinder::type('file')->prune('.svn')->discard('.svn')->maxdepth(1)->name($logfile.'_*.log')->in($logdir.'/history/');

        // if the number of logs in history exceeds history then remove the oldest log
        if(count($logs) > $history)
        {
          unlink($logs[0]);
        }
        // schedule the next rotation
        $meta[$logfile]['rotate_on'] = date('Ymd', strtotime('+ '.$period.' days'));

        // remember history incase the rotation is missed and you need to manually call a rotation with override
        $meta[$logfile]['history'] = $history;

        // update the ini file
        sfIni::write($meta, $metafile, true);
      }
    }
  }
}
