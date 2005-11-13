<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: var.class.php 434 2005-09-08 08:12:29Z fabien $
 */
class sfLog_var extends sfLog
{
    /**
     * String containing the format of a log line.
     * @var string
     * @access private
     */
    private $_lineFormat = '%1$s %2$s [%3$s] %4$s';

    /**
     * String containing the timestamp format.  It will be passed directly to
     * strftime().  Note that the timestamp string will generated using the
     * current locale.
     * @var string
     * @access private
     */
    private $_timeFormat = '%b %d %H:%M:%S';

    /**
     * Hash that maps canonical format keys to position arguments for the
     * "line format" string.
     * @var array
     * @access private
     */
    private $_formatMap = array('%{timestamp}'  => '%1$s',
                            '%{ident}'      => '%2$s',
                            '%{priority}'   => '%3$s',
                            '%{message}'    => '%4$s',
                            '%\{'           => '%%{');

    /**
     * String containing the end-on-line character sequence.
     * @var string
     * @access private
     */
    private $_eol = "\n";

    private $web_debug = null;

    public function sfLog_var($name, $ident = '', $conf = array(), $level = PEAR_LOG_DEBUG)
    {
      $this->_id = $name.'_'.$ident;
      $this->_ident = $ident;
      $this->_mask = sfLog::UPTO($level);

      if (!empty($conf['lineFormat']))
      {
        $this->_lineFormat = str_replace(array_keys($this->_formatMap), array_values($this->_formatMap), $conf['lineFormat']);
      }

      if (!empty($conf['timeFormat']))
      {
        $this->_timeFormat = $conf['timeFormat'];
      }

      if (!empty($conf['eol']))
      {
        $this->_eol = $conf['eol'];
      }
      else
      {
        $this->_eol = (strstr(PHP_OS, 'WIN')) ? "\r\n" : "\n";
      }

      $this->web_debug = sfWebDebug::getInstance();
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
            $tmp = 'called at "'.$stack['file'].'" line '.$stack['line'];
            if (isset($stack['function']))
            {
              $tmp .= ' from "'.$stack['function'].'"';
            }
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
      $this->web_debug->log($logEntry);

      /* Notify observers about this log message. */
      $this->_announce(array('priority' => $priority, 'message' => $message));

      return true;
    }
}

?>