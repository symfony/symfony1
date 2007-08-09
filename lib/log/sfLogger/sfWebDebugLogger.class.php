<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugLogger logs messages into the web debug toolbar.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugLogger extends sfLogger
{
  protected
    $context  = null,
    $buffer   = array(),
    $webDebug = null;

  /**
   * Initializes this logger.
   *
   * @param array Logger options
   */
  public function initialize($options = array())
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return;
    }

    $this->buffer  = array();
    $this->context = sfContext::getInstance();

    return parent::initialize($options);
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  protected function doLog($message, $priority)
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return;
    }

    // if we have xdebug, add some stack information
    $debugStack = array();
    if (function_exists('xdebug_get_function_stack'))
    {
      foreach (xdebug_get_function_stack() as $i => $stack)
      {
        if (
          (isset($stack['function']) && !in_array($stack['function'], array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug', 'log')))
          || !isset($stack['function'])
        )
        {
          $tmp = '';
          if (isset($stack['function']))
          {
            $tmp .= 'in "'.$stack['function'].'" ';
          }
          $tmp .= 'from "'.$stack['file'].'" line '.$stack['line'];
          $debugStack[] = $tmp;
        }
      }
    }

    // get log type in {}
    $type = 'sfOther';
    if (preg_match('/^\s*{([^}]+)}\s*(.+?)$/', $message, $matches))
    {
      $type    = $matches[1];
      $message = $matches[2];
    }

    // build the object containing the complete log information
    $logEntry = array(
      'priority'   => $priority,
      'time'       => time(),
      'message'    => $message,
      'type'       => $type,
      'debugStack' => $debugStack,
    );

    // send the log object
    if (is_null($this->webDebug))
    {
      $this->buffer[] = $logEntry;

      if ($this->context->has('sf_web_debug'))
      {
        $this->webDebug = $this->context->get('sf_web_debug');
        while ($buffer = array_shift($this->buffer))
        {
          $this->webDebug->log($buffer);
        }
      }
    }
    else
    {
      $this->webDebug->log($logEntry);
    }
  }
}
