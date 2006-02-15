<?php
/**
 * $Header: /repository/pear/sfLog/sfLog/composite.php,v 1.23 2004/08/09 06:04:11 jon Exp $
 * $Horde: horde/lib/sfLog/composite.php,v 1.2 2000/06/28 21:36:13 jon Exp $
 *
 * @version $Revision$
 * @package sfLog
 */

/**
 * The sfLog_composite:: class implements a Composite pattern which
 * allows multiple sfLog implementations to receive the same events.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@php.net>
 *
 * @since Horde 1.3
 * @since sfLog 1.0
 * @package sfLog
 */
class sfLog_composite extends sfLog
{
    /**
     * Array holding all of the sfLog instances to which log events should be
     * sent.
     *
     * @var array
     * @access private
     */
    protected $_children = array();


    /**
     * Constructs a new composite sfLog object.
     *
     * @param boolean   $name       This parameter is ignored.
     * @param boolean   $ident      This parameter is ignored.
     * @param boolean   $conf       This parameter is ignored.
     * @param boolean   $level      This parameter is ignored.
     *
     * @access public
     */
    function sfLog_composite($name = false, $ident = false, $conf = false,
                           $level = SF_PEAR_LOG_DEBUG)
    {
    }

    /**
     * Opens the child connections.
     *
     * @access public
     */
    function open()
    {
        if (!$this->_opened) {
            foreach ($this->_children as $id => $child) {
                $this->_children[$id]->open();
            }
            $this->_opened = true;
        }
    }

    /**
     * Closes any child instances.
     *
     * @access public
     */
    function close()
    {
        if ($this->_opened) {
            foreach ($this->_children as $id => $child) {
                $this->_children[$id]->close();
            }
            $this->_opened = false;
        }
    }

    /**
     * Flushes all open child instances.
     *
     * @access public
     * @since sfLog 1.8.2
     */
    function flush()
    {
        if ($this->_opened) {
            foreach ($this->_children as $id => $child) {
                $this->_children[$id]->flush();
            }
        }
    }

    /**
     * Sends $message and $priority to each child of this composite.
     *
     * @param mixed     $message    String or object containing the message
     *                              to log.
     * @param string    $priority   (optional) The priority of the message.
     *                              Valid values are: SF_PEAR_LOG_EMERG,
     *                              SF_PEAR_LOG_ALERT, SF_PEAR_LOG_CRIT,
     *                              SF_PEAR_LOG_ERR, SF_PEAR_LOG_WARNING,
     *                              SF_PEAR_LOG_NOTICE, SF_PEAR_LOG_INFO, and
     *                              SF_PEAR_LOG_DEBUG.
     *
     * @return boolean  True if the entry is successfully logged.
     *
     * @access public
     */
    function log($message, $priority = null)
    {
        /* If a priority hasn't been specified, use the default value. */
        if ($priority === null) {
            $priority = $this->_priority;
        }

        foreach ($this->_children as $id => $child) {
            $this->_children[$id]->log($message, $priority);
        }

        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }

    /**
     * Returns true if this is a composite.
     *
     * @return boolean  True if this is a composite class.
     *
     * @access public
     */
    function isComposite()
    {
        return true;
    }

    /**
     * Sets this identification string for all of this composite's children.
     *
     * @param string    $ident      The new identification string.
     *
     * @access public
     * @since  sfLog 1.6.7
     */
    function setIdent($ident)
    {
        foreach ($this->_children as $id => $child) {
            $this->_children[$id]->setIdent($ident);
        }
    }

    /**
     * Adds a sfLog instance to the list of children.
     *
     * @param object    $child      The sfLog instance to add.
     *
     * @return boolean  True if the sfLog instance was successfully added.
     *
     * @access public
     */
    function addChild(&$child)
    {
        /* Make sure this is a sfLog instance. */
        if (!($child instanceof sfLog)) {
            return false;
        }

        $this->_children[$child->_id] = &$child;

        return true;
    }

    /**
     * Removes a sfLog instance from the list of children.
     *
     * @param object    $child      The sfLog instance to remove.
     *
     * @return boolean  True if the sfLog instance was successfully removed.
     *
     * @access public
     */
    function removeChild($child)
    {
        if (!($child instanceof sfLog) || !isset($this->_children[$child->_id])) {
            return false;
        }

        unset($this->_children[$child->_id]);

        return true;
    }
}

?>
