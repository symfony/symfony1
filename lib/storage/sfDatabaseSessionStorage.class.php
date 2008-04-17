<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all sfStorage that uses a sfDatabase object as a storage.
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfDatabaseSessionStorage extends sfSessionStorage
{
  protected
    $db = null;

  /**
   * Available options:
   *
   *   * db_table:    The database table in which session data will be stored
   *   * database:    The sfDatabase object to use
   *   * db_id_col:   The database column in which the session id will be stored (sess_id by default)
   *   * db_data_col: The database column in which the session data will be stored (sess_data by default)
   *   * db_time_col: The database column in which the session timestamp will be stored (sess_time by default)
   *
   * @see sfSessionStorage
   */
  public function initialize($options = array())
  {
    $options = array_merge(array(
      'db_id_col'   => 'sess_id',
      'db_data_col' => 'sess_data',
      'db_time_col' => 'sess_time',
    ), $options);

    // disable auto_start
    $options['auto_start'] = false;

    // initialize the parent
    parent::initialize($options);

    if (!isset($this->options['db_table']))
    {
      throw new sfInitializationException('You must provide a "db_table" option to sfDatabaseSessionStorage.');
    }

    if (!isset($this->options['database']))
    {
      throw new sfInitializationException('You must provide a "database" option to sfDatabaseSessionStorage.');
    }

    // use this object as the session handler
    session_set_save_handler(array($this, 'sessionOpen'),
                             array($this, 'sessionClose'),
                             array($this, 'sessionRead'),
                             array($this, 'sessionWrite'),
                             array($this, 'sessionDestroy'),
                             array($this, 'sessionGC'));

    // start our session
    session_start();
  }

  /**
   * Closes a session.
   *
   * @return boolean true, if the session was closed, otherwise false
   */
  public function sessionClose()
  {
    // do nothing
    return true;
  }

  /**
   * Opens a session.
   *
   * @param string
   * @param string
   *
   * @return boolean true, if the session was opened, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If a connection with the database does not exist or cannot be created
   */
  public function sessionOpen($path, $name)
  {
    // what database are we using?
    $database = $this->options['database'];

    // get the database resource
    $this->db = $database->getConnection();
    if (is_null($this->db))
    {
      throw new sfDatabaseException('Database connection doesn\'t exist. Unable to open session.');
    }

    return true;
  }

  /**
   * Destroys a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was destroyed, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session cannot be destroyed
   */
  abstract public function sessionDestroy($id);

  /**
   * Cleans up old sessions.
   *
   * @param int The lifetime of a session
   *
   * @return boolean true, if old sessions have been cleaned, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If any old sessions cannot be cleaned
   */
  abstract public function sessionGC($lifetime);

  /**
   * Reads a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was read, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session cannot be read
   */
  abstract public function sessionRead($id);

  /**
   * Writes session data.
   *
   * @param string A session ID
   * @param string A serialized chunk of session data
   *
   * @return boolean true, if the session was written, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session data cannot be written
   */
  abstract public function sessionWrite($id, $data);

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
  }
}
