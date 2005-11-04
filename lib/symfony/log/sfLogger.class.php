<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger manage all logging in symfony projects. It implements the Singleton pattern.
 * It's a wrapper around Pear_Log class. Logs are stored in the SF_APP.log file in SF_LOG_DIR directory.
 * If SF_STATS_DEBUG is true, all logging information is also available trough the web debug console.
 *
 * Logging can be controlled by 2 constants:
 * - SF_LOGGING_ACTIVE: set to false to disable all logging
 * - SF_LOGGING_LEVEL:  level of logging
 *
 * Same log levels as Pear_Log.
 * This list is ordered by highest priority (PEAR_LOG_EMERG) to lowest priority (PEAR_LOG_DEBUG):
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
 * @version    SVN: $Id: sfLogger.class.php 434 2005-09-08 08:12:29Z fabien $
 */
class sfLogger extends Log
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
      if (SF_LOGGING_ACTIVE)
      {
        require_once 'symfony/log/Log/composite.class.php';
        require_once 'symfony/log/Log/file.class.php';
        $logger = &Log::singleton('composite');
        $conf = array('mode' => 0666);
        $file_logger = &Log::singleton('file', SF_LOG_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.log', 'symfony', $conf);
        $file_logger->setMask(Log::UPTO(constant('PEAR_LOG_'.strtoupper(SF_LOGGING_LEVEL))));
        $logger->addChild($file_logger);

        if (defined('SF_WEB_DEBUG') && SF_WEB_DEBUG)
        {
          require_once 'symfony/log/sfLogger/var.class.php';
          $var_logger = &Log::singleton('var', '', 'symfony');
          $var_logger->setMask(Log::UPTO(constant('PEAR_LOG_'.strtoupper(SF_LOGGING_LEVEL))));
          $logger->addChild($var_logger);
        }

        sfLogger::$logger = $logger;
      }
      else
      {
        require_once 'symfony/log/sfLogger/no.class.php';
        sfLogger::$logger = new sfNoLogger();
      }
    }

    return sfLogger::$logger;
  }

  public static function errorHandler($code, $message, $file, $line)
  {
    /* Map the PHP error to a Log priority. */
    switch ($code)
    {
      case E_WARNING:
      case E_USER_WARNING:
        $priority = PEAR_LOG_WARNING;
        break;
      case E_NOTICE:
      case E_USER_NOTICE:
        $priority = PEAR_LOG_NOTICE;
        break;
      case E_ERROR:
      case E_USER_ERROR:
        $priority = PEAR_LOG_ERR;
        break;
      default:
        $priority = PEAR_LOG_INFO;
    }

    sfLogger::$logger->log($message.' in '.$file.' at line '.$line, $priority);

    die();
  }
}

?>