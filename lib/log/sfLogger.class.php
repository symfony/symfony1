<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger manages all logging in symfony projects.
 *
 * sfLogger can be configuration via the logging.yml configuration file.
 * Loggers can also be registered directly in the logging.yml configuration file.
 *
 * This level list is ordered by highest priority (self::EMERG) to lowest priority (self::DEBUG):
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
  const EMERG   = 0; // System is unusable
  const ALERT   = 1; // Immediate action required
  const CRIT    = 2; // Critical conditions
  const ERR     = 3; // Error conditions
  const WARNING = 4; // Warning conditions
  const NOTICE  = 5; // Normal but significant
  const INFO    = 6; // Informational
  const DEBUG   = 7; // Debug-level messages

  protected
    $loggers = array(),
    $level   = self::INFO;

  protected static
    $logger = null;

  /**
   * Returns the sfLogger instance.
   *
   * @return  object the sfLogger instance
   */
  public static function getInstance()
  {
    if (!self::$logger)
    {
      // the class exists
      $class = __CLASS__;
      self::$logger = new $class();
      self::$logger->initialize();
    }

    return self::$logger;
  }

  /**
   * Initializes the logger.
   */
  public function initialize()
  {
    $this->loggers = array();
  }

  /**
   * Retrieves the log level for the current logger instance.
   *
   * @return string Log level
   */
  public function getLogLevel()
  {
    return $this->level;
  }

  /**
   * Sets a log level for the current logger instance.
   *
   * @param string Log level
   */
  public function setLogLevel($level)
  {
    $this->level = $level;
  }
  
  /**
   * Retrieves current loggers.
   *
   * @return array List of loggers
   */
  public function getLoggers()
  {
    return $this->loggers;
  }
  
  /**
   * Registers a logger.
   *
   * @param string Logger name
   */
  public function registerLogger(sfLoggerInterface $logger)
  {
    $this->loggers[] = $logger;
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  public function log($message, $priority = self::INFO)
  {
    if ($this->level < $priority)
    {
      return;
    }

    foreach ($this->loggers as $logger)
    {
      $logger->log((string) $message, $priority);
    }
  }

  /**
   * Logs an emerg message.
   *
   * @param string Message
   */
  public function emerg($message)
  {
    $this->log($message, self::EMERG);
  }

  /**
   * Logs an alert message.
   *
   * @param string Message
   */
  public function alert($message)
  {
    $this->log($message, self::ALERT);
  }

  /**
   * Logs a critical message.
   *
   * @param string Message
   */
  public function crit($message)
  {
    $this->log($message, self::CRIT);
  }

  /**
   * Logs an error message.
   *
   * @param string Message
   */
  public function err($message)
  {
    $this->log($message, self::ERR);
  }

  /**
   * Logs a warning message.
   *
   * @param string Message
   */
  public function warning($message)
  {
    $this->log($message, self::WARNING);
  }

  /**
   * Logs a notice message.
   *
   * @param string Message
   */
  public function notice($message)
  {
    $this->log($message, self::NOTICE);
  }

  /**
   * Logs an info message.
   *
   * @param string Message
   */
  public function info($message)
  {
    $this->log($message, self::INFO);
  }

  /**
   * Logs a debug message.
   *
   * @param string Message
   */
  public function debug($message)
  {
    $this->log($message, self::DEBUG);
  }

  /**
   * Executes the shutdown procedure.
   *
   * Cleans up the current logger instance.
   */
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

  public static function getPriorityName($priority)
  {
    static $levels  = array(
      self::EMERG   => 'emerg',
      self::ALERT   => 'alert',
      self::CRIT    => 'crit',
      self::ERR     => 'err',
      self::WARNING => 'warning',
      self::NOTICE  => 'notice',
      self::INFO    => 'info',
      self::DEBUG   => 'debug',
    );

    if (!isset($levels[$priority]))
    {
      throw new sfException(sprintf('The priority level "%s" does not exist.', $priority));
    }

    return $levels[$priority];
  }
}
