<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger is the abstract class for all logging classes.
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
abstract class sfLogger
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
    $level = self::INFO;

  /**
   * Retrieves a new sfLogger implementation instance.
   *
   * @param string A sfLogger implementation name
   *
   * @return User A sfLogger implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a logger implementation instance cannot
   */
  public static function newInstance($class)
  {
    $object = new $class();

    if ($object instanceof sfLoggerInterface)
    {
      return new sfLoggerWrapper($object);
    }

    if (!$object instanceof sfLogger)
    {
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfLogger.', $class));
    }

    return $object;
  }

  /**
   * Initializes this sfLogger instance.
   *
   * @param array An associative array of initialization parameters.
   *
   * Available options:
   *
   * - level: The log level.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this User.
   */
  public function initialize($parameters = array())
  {
    if (isset($parameters['level']))
    {
      $this->setLogLevel($parameters['level']);
    }
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
    if (!is_int($level))
    {
      $level = constant('sfLogger::'.strtoupper($level));
    }

    $this->level = $level;
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
      return false;
    }

    return $this->doLog($message, $priority);
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  abstract protected function doLog($message, $priority);

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
  }

  /**
   * Returns the priority name given a priority class constant
   *
   * @param  integer A priority class constant
   *
   * @return string  The priority name
   *
   * @throws sfException if the priority level does not exist
   */
  static public function getPriorityName($priority)
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
