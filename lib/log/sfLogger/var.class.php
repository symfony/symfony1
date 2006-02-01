<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLog_var extends sfLog
{
    /**
     * String containing the end-on-line character sequence.
     * @var string
     * @access private
     */
    private $_eol = "\n";

    public function sfLog_var($name, $ident = '', $conf = array(), $level = SF_PEAR_LOG_DEBUG)
    {
      $this->_id = $name.'_'.$ident;
      $this->_ident = $ident;
      $this->_mask = sfLog::UPTO($level);

      if (!empty($conf['eol']))
      {
        $this->_eol = $conf['eol'];
      }
      else
      {
        $this->_eol = (strstr(PHP_OS, 'WIN')) ? "\r\n" : "\n";
      }
    }

    public function log($message, $priority = null)
    {
      /* Abort early if the priority is above the maximum logging level. */
      if (!$this->_isMasked($priority))
      {
        return false;
      }

      $logEntry = new sfLogEntry();

      /* If a priority hasn't been specified, use the default value. */
      if ($priority === null)
      {
        $priority = $this->_priority;
      }

      /* Extract the string representation of the message. */
      $message = $this->_extractMessage($message);

      /* If we have xdebug, add some stack information */
      $debug_stack = array();
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
            $debug_stack[] = $tmp;
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

      /* Build the object containing the complete log information. */
      $logEntry->setPriority($priority);
      $logEntry->setPriorityString($this->priorityToString($priority));
      $logEntry->setTime(time());
      $logEntry->setMessage($message);
      $logEntry->setType($type);
      $logEntry->setDebugStack($debug_stack);

      /* Send the log object. */
      sfWebDebug::getInstance()->log($logEntry);

      /* Notify observers about this log message. */
      $this->_announce(array('priority' => $priority, 'message' => $message));

      return true;
    }
}

?>