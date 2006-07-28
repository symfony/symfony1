<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger manage all logging in symfony projects. It implements the Singleton pattern.
 * It's a wrapper around Pear_sfLog class. sfLogs are stored in the [sf_app].log file in [sf_log_dir] directory.
 * If [sf_stats_debug] is true, all logging information is also available trough the web debug console.
 *
 * sfLogging can be controlled by 2 constants:
 * - [sf_logging_active]: set to false to disable all logging
 * - [sf_logging_level]:  level of logging
 *
 * Same log levels as Pear_sfLog.
 * This list is ordered by highest priority (SF_PEAR_LOG_EMERG) to lowest priority (SF_PEAR_LOG_DEBUG):
 * - EMERG:   System is unusable
 * - ALERT:   Immediate action required
 * - CRIT:    Critical conditions
 * - ERR:     Error conditions
 * - WARNING: Warning conditions
 * - NOTICE:  Normal but significant
 * - INFO:    Informational
 * - DEBUG:   Debug-level messages
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogger extends sfLog
{
  private static $logger = null;

  /**
   * Returns the sfLogger instance.
   *
   * @return  object the sfLogger instance
   */
  public static function getInstance()
  {
    if (!sfLogger::$logger)
    {
      if (sfConfig::get('sf_logging_active'))
      {
        $sf_symfony_lib_dir = sfConfig::get('sf_symfony_lib_dir');
        require_once $sf_symfony_lib_dir.'/log/sfLog/composite.class.php';
        require_once $sf_symfony_lib_dir.'/log/sfLog/file.class.php';
        $logger = &sfLog::singleton('composite');
        $conf = array('mode' => 0666);
        $file_logger = &sfLog::singleton('file', sfConfig::get('sf_log_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app').'_'.sfConfig::get('sf_environment').'.log', 'symfony', $conf);
        $file_logger->setMask(sfLog::UPTO(constant('SF_PEAR_LOG_'.strtoupper(sfConfig::get('sf_logging_level')))));
        $logger->addChild($file_logger);

        if (sfConfig::get('sf_web_debug'))
        {
          require_once $sf_symfony_lib_dir.'/log/sfLogger/var.class.php';
          $var_logger = &sfLog::singleton('var', '', 'symfony');
          $var_logger->setMask(sfLog::UPTO(constant('SF_PEAR_LOG_'.strtoupper(sfConfig::get('sf_logging_level')))));
          $logger->addChild($var_logger);
        }

        sfLogger::$logger = $logger;
      }
      else
      {
        require_once sfConfig::get('sf_symfony_lib_dir').'/log/sfLogger/no.class.php';
        sfLogger::$logger = new sfNoLogger();
      }
    }

    return sfLogger::$logger;
  }
}
