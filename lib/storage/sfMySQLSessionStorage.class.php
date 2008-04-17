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
 * Provides support for session storage using a MySQL brand database.
 *
 * <b>parameters:</b> see sfDatabaseSessionStorage
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfMySQLSessionStorage extends sfDatabaseSessionStorage
{
  /**
   * Destroys a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was destroyed, otherwise an exception is thrown
   *
   * @throws <b>sfDatabaseException</b> If the session cannot be destroyed.
   */
  public function sessionDestroy($id)
  {
    // get table/column
    $db_table  = $this->options['db_table'];
    $db_id_col = $this->options['db_id_col'];

    // cleanup the session id, just in case
    $id = mysql_real_escape_string($id, $this->db->getResource());

    // delete the record associated with this id
    $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_id_col.' = \''.$id.'\'';

    if (@mysql_query($sql, $this->db->getResource()))
    {
      return true;
    }

    // failed to destroy session
    throw new sfDatabaseException(sprintf('sfMySQLSessionStorage cannot destroy session id "%s" (%s).', $id, mysql_error()));
  }

  /**
   * Cleans up old sessions.
   *
   * @param int The lifetime of a session
   *
   * @return boolean true, if old sessions have been cleaned, otherwise an exception is thrown
   *
   * @throws <b>sfDatabaseException</b> If any old sessions cannot be cleaned
   */
  public function sessionGC($lifetime)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_time_col = $this->options['db_time_col'];

    // delete the record associated with this id
    $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_time_col.' < '.(time() - $lifetime);

    if (!@mysql_query($sql, $this->db->getResource()))
    {
      throw new sfDatabaseException(sprintf('sfMySQLSessionStorage cannot delete old sessions (%s).', mysql_error()));
    }

    return true;
  }

  /**
   * Reads a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was read, otherwise an exception is thrown
   *
   * @throws <b>sfDatabaseException</b> If the session cannot be read
   */
  public function sessionRead($id)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_data_col = $this->options['db_data_col'];
    $db_id_col   = $this->options['db_id_col'];
    $db_time_col = $this->options['db_time_col'];

    // cleanup the session id, just in case
    $id = mysql_real_escape_string($id, $this->db->getResource());

    // delete the record associated with this id
    $sql = 'SELECT '.$db_data_col.' FROM '.$db_table.' WHERE '.$db_id_col.' = \''.$id.'\'';

    $result = @mysql_query($sql, $this->db->getResource());

    if ($result != false && @mysql_num_rows($result) == 1)
    {
      // found the session
      $data = mysql_fetch_row($result);

      return $data[0];
    }
    else
    {
      // session does not exist, create it
      $sql = 'INSERT INTO '.$db_table.' ('.$db_id_col.', '.$db_data_col.', '.$db_time_col.') VALUES (\''.$id.'\', \'\', '.time().')';

      if (@mysql_query($sql, $this->db->getResource()))
      {
        return '';
      }

      // can't create record
      throw new sfDatabaseException(sprintf('sfMySQLSessionStorage cannot create new record for id "%s" (%s).', $id, mysql_error()));
    }
  }

  /**
   * Writes session data.
   *
   * @param string A session ID
   * @param string A serialized chunk of session data
   *
   * @return boolean true, if the session was written, otherwise an exception is thrown
   *
   * @throws <b>sfDatabaseException</b> If the session data cannot be written
   */
  public function sessionWrite($id, $data)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_data_col = $this->options['db_data_col'];
    $db_id_col   = $this->options['db_id_col'];
    $db_time_col = $this->options['db_time_col'];

    // cleanup the session id and data, just in case
    $id   = mysql_real_escape_string($id, $this->db->getResource());
    $data = mysql_real_escape_string($data, $this->db->getResource());

    // delete the record associated with this id
    $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.' = \''.$data.'\', '.$db_time_col.' = '.time().' WHERE '.$db_id_col.' = \''.$id.'\'';

    if (@mysql_query($sql, $this->db->getResource()))
    {
      return true;
    }

    // failed to write session data
    throw new sfDatabaseException(sprintf('sfMySQLSessionStorage cannot write session data for id "%s" (%s).', $id, mysql_error()));
  }
}
