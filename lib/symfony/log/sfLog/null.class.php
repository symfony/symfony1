<?php
/**
 * $Header: /repository/pear/sfLog/sfLog/null.php,v 1.3 2004/01/19 08:02:40 jon Exp $
 *
 * @version $Revision: 1.3 $
 * @package sfLog
 */

/**
 * The sfLog_null class is a concrete implementation of the sfLog:: abstract
 * class.  It simply consumes log events.
 * 
 * @author  Jon Parise <jon@php.net>
 * @since   sfLog 1.8.2
 * @package sfLog
 *
 * @example null.php    Using the null handler.
 */
class sfLog_null extends sfLog
{
    /**
     * Constructs a new sfLog_null object.
     * 
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $level    sfLog messages up to and including this level.
     * @access public
     */
    function sfLog_null($name, $ident = '', $conf = array(),
					  $level = PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        $this->_ident = $ident;
        $this->_mask = sfLog::UPTO($level);
    }

    /**
     * Simply consumes the log event.  The message will still be passed
     * along to any sfLog_observer instances that are observing this sfLog.
     * 
     * @param mixed  $message    String or object containing the message to log.
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     * @return boolean  True on success or false on failure.
     * @access public
     */
    function log($message, $priority = null)
    {
        /* If a priority hasn't been specified, use the default value. */
        if ($priority === null) {
            $priority = $this->_priority;
        }

        /* Abort early if the priority is above the maximum logging level. */
        if (!$this->_isMasked($priority)) {
            return false;
        }

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }
}

?>
