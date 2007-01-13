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
 *
 * sfLogger can be configuration via the logging.yml configuration file.
 * Loggers can also be registered directly in the logging.yml configuration file.
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
  }

  public function getLogLevel($level)
  {
    return $this->level;
  }

  public function setLogLevel($level)
  {
    $this->level = $level;
  }

  public function getLoggers()
  {
    return $this->loggers;
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
