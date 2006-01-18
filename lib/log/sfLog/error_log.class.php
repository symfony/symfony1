<?php
/**
 * $Header: /repository/pear/sfLog/sfLog/error_log.php,v 1.6 2004/01/19 08:02:40 jon Exp $
 *
 * @version $Revision: 1.6 $
 * @package sfLog
 */

/**
 * The sfLog_error_log class is a concrete implementation of the sfLog abstract
 * class that logs messages using PHP's error_log() function.
 * 
 * @author  Jon Parise <jon@php.net>
 * @since   sfLog 1.7.0
 * @package sfLog
 * 
 * @example error_log.php   Using the error_log handler.
 */
class sfLog_error_log extends sfLog
{
    /**
     * The error_log() log type.
     * @var integer
     * @access private
     */
    var $_type = PEAR_LOG_TYPE_SYSTEM;

    /**
     * The type-specific destination value.
     * @var string
     * @access private
     */
    var $_destination = '';

    /**
     * Additional headers to pass to the mail() function when the
     * PEAR_LOG_TYPE_MAIL type is used.
     * @var string
     * @access private
     */
    var $_extra_headers = '';

    /**
     * Constructs a new sfLog_error_log object.
     * 
     * @param string $name     Ignored.
     * @param string $ident    The identity string.
     * @param array  $conf     The configuration array.
     * @param int    $level    sfLog messages up to and including this level.
     * @access public
     */
    function sfLog_error_log($name, $ident = '', $conf = array(),
                           $level = PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        $this->_type = $name;
        $this->_ident = $ident;
        $this->_mask = sfLog::UPTO($level);

        if (!empty($conf['destination'])) {
            $this->_destination = $conf['destination'];
        }
        if (!empty($conf['extra_headers'])) {
            $this->_extra_headers = $conf['extra_headers'];
        }
    }

    /**
     * sfLogs $message using PHP's error_log() function.  The message is also
     * passed along to any sfLog_observer instances that are observing this sfLog.
     * 
     * @param mixed  $message   String or object containing the message to log.
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

        /* Extract the string representation of the message. */
        $message = $this->_extractMessage($message);

        $success = error_log($this->_ident . ': ' . $message, $this->_type,
                             $this->_destination, $this->_extra_headers);

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return $success;
    }
}

?>
