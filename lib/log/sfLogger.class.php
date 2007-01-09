<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('SF_LOG_EMERG',   0); // System is unusable
define('SF_LOG_ALERT',   1); // Immediate action required
define('SF_LOG_CRIT',    2); // Critical conditions
define('SF_LOG_ERR',     3); // Error conditions
define('SF_LOG_WARNING', 4); // Warning conditions
define('SF_LOG_NOTICE',  5); // Normal but significant
define('SF_LOG_INFO',    6); // Informational
define('SF_LOG_DEBUG',   7); // Debug-level messages

/**
 * sfLogger manages all logging in symfony projects.
 * Logs are stored in the [sf_app].log file in [sf_log_dir] directory.
 * If [sf_web_debug] is true, all logging information is also available trough the web debug console.
 *
 * sfLogger can be controlled by 2 constants:
 * - [sf_logging_enabled]: set to false to disable all logging
 * - [sf_logging_level]:  level of logging
 *
 * This level list is ordered by highest priority (SF_LOG_EMERG) to lowest priority (SF_LOG_DEBUG):
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
class sfLogger
{
  protected
    $loggers = array(),
    $level   = SF_LOG_EMERG,
    $levels  = array(
      SF_LOG_EMERG   => 'emergency',
      SF_LOG_ALERT   => 'alert',
      SF_LOG_CRIT    => 'critical',
      SF_LOG_ERR     => 'error',
      SF_LOG_WARNING => 'warning',
      SF_LOG_NOTICE  => 'notice',
      SF_LOG_INFO    => 'info',
      SF_LOG_DEBUG   => 'debug',
    );

  protected static
    $logger = null;

  /**
   * Returns the sfLogger instance.
   *
   * @return  object the sfLogger instance
   */
  public static function getInstance()
  {
    if (!sfLogger::$logger)
    {
      // the class exists
      $class = __CLASS__;
      sfLogger::$logger = new $class();
      sfLogger::$logger->initialize();
    }

    return sfLogger::$logger;
  }

  public function initialize()
  {
    $this->loggers = array();

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->level = constant('SF_LOG_'.strtoupper(sfConfig::get('sf_logging_level')));

      $webDebugLogger = new sfWebDebugLogger();
      $webDebugLogger->initialize();
      $this->loggers[] = $webDebugLogger;

      $fileLogger = new sfFileLogger();
      $options = array(
        'file' => sfConfig::get('sf_log_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app').'_'.sfConfig::get('sf_environment').'.log',
      );
      $fileLogger->initialize($options);
      $this->loggers[] = $fileLogger;
    }
  }

  public function registerLogger($logger)
  {
    $this->loggers[] = $logger;
  }

  public function log($message, $priority = null)
  {
    if (!$priority)
    {
      $priority = SF_LOG_INFO;
    }

    if ($this->level < $priority)
    {
      return;
    }

    foreach ($this->loggers as $logger)
    {
      $logger->log((string) $message, $priority, $this->levels[$priority]);
    }
  }

  public function emerg($message)
  {
    $this->log($message, SF_LOG_EMERG);
  }

  public function alert($message)
  {
    $this->log($message, SF_LOG_ALERT);
  }

  public function crit($message)
  {
    $this->log($message, SF_LOG_CRIT);
  }

  public function err($message)
  {
    $this->log($message, SF_LOG_ERR);
  }

  public function warning($message)
  {
    $this->log($message, SF_LOG_WARNING);
  }

  public function notice($message)
  {
    $this->log($message, SF_LOG_NOTICE);
  }

  public function info($message)
  {
    $this->log($message, SF_LOG_INFO);
  }

  public function debug($message)
  {
    $this->log($message, SF_LOG_DEBUG);
  }

  public function shutdown()
  {
    foreach ($this->loggers as $logger)
    {
      if (method_exists($logger, 'shutdown'))
      {
        $logger->shutdown();
      }
    }

    $this->loggers = array();
  }
}
