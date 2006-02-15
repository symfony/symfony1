<?php
/**
 * $Header: /repository/pear/sfLog/sfLog/sql.php,v 1.34 2004/08/19 06:35:57 jon Exp $
 * $Horde: horde/lib/sfLog/sql.php,v 1.12 2000/08/16 20:27:34 chuck Exp $
 *
 * @version $Revision$
 * @package sfLog
 */

/** PEAR's DB package */
require_once 'DB.php';

/**
 * The sfLog_sql class is a concrete implementation of the sfLog::
 * abstract class which sends messages to an SQL server.  Each entry
 * occupies a separate row in the database.
 *
 * This implementation uses PHP's PEAR database abstraction layer.
 *
 * CREATE TABLE log_table (
 *  id          INT NOT NULL,
 *  logtime     TIMESTAMP NOT NULL,
 *  ident       CHAR(16) NOT NULL,
 *  priority    INT NOT NULL,
 *  message     VARCHAR(200),
 *  PRIMARY KEY (id)
 * );
 *
 * @author  Jon Parise <jon@php.net>
 * @since   Horde 1.3
 * @since   sfLog 1.0
 * @package sfLog 
 */
class sfLog_sql extends sfLog {

    /** 
     * Array containing the dsn information. 
     * @var string
     * @access private
     */
    var $_dsn = '';

    /** 
     * Object holding the database handle. 
     * @var object
     * @access private
     */
    var $_db = null;

    /**
     * Flag indicating that we're using an existing database connection.
     * @var boolean
     * @access private
     */
    var $_existingConnection = false;

    /** 
     * String holding the database table to use. 
     * @var string
     * @access private
     */
    var $_table = 'log_table';

    /**
     * String holding the name of the ID sequence.
     * @var string
     * @access private
     */
    var $_sequence = 'log_id';

    /**
     * Maximum length of the $ident string.  This corresponds to the size of
     * the 'ident' column in the SQL table.
     * @var integer
     * @access private
     */
    var $_identLimit = 16;


    /**
     * Constructs a new sql logging object.
     *
     * @param string $name         The target SQL table.
     * @param string $ident        The identification field.
     * @param array $conf          The connection configuration array.
     * @param int $level           sfLog messages up to and including this level.
     * @access public     
     */
    function sfLog_sql($name, $ident = '', $conf = array(),
                     $level = SF_PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        $this->_table = $name;
        $this->_mask = sfLog::UPTO($level);

        /* If a specific sequence name was provided, use it. */
        if (!empty($conf['sequence'])) {
            $this->_sequence = $conf['sequence'];
        }

        /* If a specific sequence name was provided, use it. */
        if (isset($conf['identLimit'])) {
            $this->_identLimit = $conf['identLimit'];
        }

        /* Now that the ident limit is confirmed, set the ident string. */
        $this->setIdent($ident);

        /* If an existing database connection was provided, use it. */
        if (isset($conf['db'])) {
            $this->_db = &$conf['db'];
            $this->_existingConnection = true;
            $this->_opened = true;
        } else {
            $this->_dsn = $conf['dsn'];
        }
    }

    /**
     * Opens a connection to the database, if it has not already
     * been opened. This is implicitly called by log(), if necessary.
     *
     * @return boolean   True on success, false on failure.
     * @access public     
     */
    function open()
    {
        if (!$this->_opened) {
            $this->_db = &DB::connect($this->_dsn, true);
            if (DB::isError($this->_db)) {
                return false;
            }
            $this->_opened = true;
        }

        return $this->_opened;
    }

    /**
     * Closes the connection to the database if it is still open and we were
     * the ones that opened it.  It is the caller's responsible to close an
     * existing connection that was passed to us via $conf['db'].
     *
     * @return boolean   True on success, false on failure.
     * @access public     
     */
    function close()
    {
        if ($this->_opened && !$this->_existingConnection) {
            $this->_opened = false;
            return $this->_db->disconnect();
        }

        return ($this->_opened === false);
    }

    /**
     * Sets this sfLog instance's identification string.  Note that this
     * SQL-specific implementation will limit the length of the $ident string
     * to sixteen (16) characters.
     *
     * @param string    $ident      The new identification string.
     *
     * @access  public
     * @since   sfLog 1.8.5
     */
    function setIdent($ident)
    {
        $this->_ident = substr($ident, 0, $this->_identLimit);
    }

    /**
     * Inserts $message to the currently open database.  Calls open(),
     * if necessary.  Also passes the message along to any sfLog_observer
     * instances that are observing this sfLog.
     *
     * @param mixed  $message  String or object containing the message to log.
     * @param string $priority The priority of the message.  Valid
     *                  values are: SF_PEAR_LOG_EMERG, SF_PEAR_LOG_ALERT,
     *                  SF_PEAR_LOG_CRIT, SF_PEAR_LOG_ERR, SF_PEAR_LOG_WARNING,
     *                  SF_PEAR_LOG_NOTICE, SF_PEAR_LOG_INFO, and SF_PEAR_LOG_DEBUG.
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

        /* If the connection isn't open and can't be opened, return failure. */
        if (!$this->_opened && !$this->open()) {
            return false;
        }

        /* Extract the string representation of the message. */
        $message = $this->_extractMessage($message);

        /* Build the SQL query for this log entry insertion. */
        $id = $this->_db->nextId($this->_sequence);
        $q = sprintf('insert into %s (id, logtime, ident, priority, message)' .
                     'values(%d, CURRENT_TIMESTAMP, %s, %d, %s)',
                     $this->_table, $id, $this->_db->quote($this->_ident),
                     $priority, $this->_db->quote($message));

        $result = $this->_db->query($q);
        if (DB::isError($result)) {
            return false;
        }

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }
}

?>
