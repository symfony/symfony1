<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides support for session storage using a PDO database abstraction layer.
 *
 * <b>parameters:</b> see sfDatabaseSessionStorage
 *
 * @package    symfony
 * @subpackage storage
 * @author     Mathew Toth <developer@poetryleague.com>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfPDOSessionStorage extends sfDatabaseSessionStorage
{
  /**
   * Destroys a session.
   *
   * @param  string $id  A session ID
   *
   * @return bool true, if the session was destroyed, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session cannot be destroyed
   */
  public function sessionDestroy($id)
  {
    // get table/column
    $db_table  = $this->options['db_table'];
    $db_id_col = $this->options['db_id_col'];

    // delete the record associated with this id
    $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_id_col.'= ?';

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR);
      $stmt->execute();
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }
    
    return true;
  }

  /**
   * Cleans up old sessions.
   *
   * @param  int $lifetime  The lifetime of a session
   *
   * @return bool true, if old sessions have been cleaned, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If any old sessions cannot be cleaned
   */
  public function sessionGC($lifetime)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_time_col = $this->options['db_time_col'];

    // delete the record associated with this id
    $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_time_col.' < '.(time() - $lifetime);

    try
    {
      $this->db->query($sql);
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }

    return true;
  }

  /**
   * Reads a session.
   *
   * @param  string $id  A session ID
   *
   * @return string      The session data if the session was read or created, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session cannot be read
   */
  public function sessionRead($id)
  {
    // get table/columns
    $db_table    = $this->options['db_table'];
    $db_data_col = $this->options['db_data_col'];
    $db_id_col   = $this->options['db_id_col'];
    $db_time_col = $this->options['db_time_col'];

    try
    {
      $sql = 'SELECT '.$db_data_col.' FROM '.$db_table.' WHERE '.$db_id_col.'=?';

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR, 255);

      $stmt->execute();
      if ($data = $stmt->fetchColumn())
      {
        return $data;
      }
      else
      {
        // session does not exist, create it
        $sql = 'INSERT INTO '.$db_table.'('.$db_id_col.', '.$db_data_col.', '.$db_time_col.') VALUES (?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_STR);
        $stmt->bindValue(2, '', PDO::PARAM_STR);
        $stmt->bindValue(3, time(), PDO::PARAM_INT);
        $stmt->execute();

        return '';
      }
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }
  }

  /**
   * Writes session data.
   *
   * @param  string $id    A session ID
   * @param  string $data  A serialized chunk of session data
   *
   * @return bool true, if the session was written, otherwise an exception is thrown
   *
   * @throws <b>DatabaseException</b> If the session data cannot be written
   */
  public function sessionWrite($id, $data)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_data_col = $this->options['db_data_col'];
    $db_id_col   = $this->options['db_id_col'];
    $db_time_col = $this->options['db_time_col'];

    $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.' = ?, '.$db_time_col.' = '.time().' WHERE '.$db_id_col.'= ?';

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $data, PDO::PARAM_STR);
      $stmt->bindParam(2, $id, PDO::PARAM_STR);
      $stmt->execute();
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }

    return true;
  }

  /**
   * Updates the session id.
   *
   * @param  string   $currentId The current session id
   * @param  string   $newId     The new current id
   * 
   * @return Boolean  True if the session id was successfully regenerated
   * @throws sfDatabaseException if an error occured while regenrating the session id
   */
  protected function updateSessionId($currentId, $newId)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_id_col   = $this->options['db_id_col'];

    // update the session id
    $sql = "UPDATE $db_table SET $db_id_col=? WHERE $db_id_col=?";

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $currentId, PDO::PARAM_STR);
      $stmt->bindParam(2, $newId, PDO::PARAM_STR);
      $stmt->execute();
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }

    return true;
  }
}
