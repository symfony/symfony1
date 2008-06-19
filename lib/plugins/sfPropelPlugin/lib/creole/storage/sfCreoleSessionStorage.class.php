<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr <sean@code-box.org>
 *
 * The original version the file is based on is licensed under the LGPL, but a special license was granted.
 * Please see the licenses/LICENSE.Agavi file
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides support for session storage using a CreoleDb database abstraction layer.
 *
 * <b>parameters:</b> see sfDatabaseSessionStorage
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @version    SVN: $Id$
 */
class sfCreoleSessionStorage extends sfDatabaseSessionStorage
{
  /**
   * Destroy a session.
   *
   * @param string $id A session ID.
   *
   * @return bool true, if the session was destroyed, otherwise an exception is thrown.
   *
   * @throws <b>DatabaseException</b> If the session cannot be destroyed.
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
      $stmt = $this->con->prepareStatement($sql);
      $stmt->setString(1, $id);
      $stmt->executeUpdate();
    }
    catch (SQLException $e)
    {
      throw new sfDatabaseException(sprintf('Creole SQLException was thrown when trying to manipulate session data. Message: %s.', $e->getMessage()));
    }

    return true;
  }

  /**
   * Cleanup old sessions.
   *
   * @param int $lifetime The lifetime of a session.
   *
   * @return bool true, if old sessions have been cleaned, otherwise an exception is thrown.
   *
   * @throws <b>DatabaseException</b> If any old sessions cannot be cleaned.
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
      $this->con->executeQuery($sql);
    }
    catch (SQLException $e)
    {
      throw new sfDatabaseException(sprintf('Creole SQLException was thrown when trying to manipulate session data. Message: %s.', $e->getMessage()));
    }

    return true;
  }

  /**
   * Read a session.
   *
   * @param string $id A session ID.
   *
   * @return bool true, if the session was read, otherwise an exception is thrown.
   *
   * @throws <b>DatabaseException</b> If the session cannot be read.
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

      $stmt = $this->con->prepareStatement($sql);
      $stmt->setString(1, $id);

      $dbRes = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);

      if ($dbRes->next())
      {
        $data = $dbRes->getString(1);

        return $data;
      }
      else
      {
        // session does not exist, create it
        $sql = 'INSERT INTO '.$db_table.'('.$db_id_col.','.$db_data_col.','.$db_time_col.') VALUES (?,?,?)';

        $stmt = $this->con->prepareStatement($sql);
        $stmt->setString(1, $id);
        $stmt->setString(2, '');
        $stmt->setInt(3, time());
        $stmt->executeUpdate();

        return '';
      }
    }
    catch (SQLException $e)
    {
      throw new sfDatabaseException(sprintf('Creole SQLException was thrown when trying to manipulate session data. Message: %s.', $e->getMessage()));
    }
  }

  /**
   * Write session data.
   *
   * @param string $id A session ID.
   * @param string $data A serialized chunk of session data.
   *
   * @return bool true, if the session was written, otherwise an exception is
   *              thrown.
   *
   * @throws <b>DatabaseException</b> If the session data cannot be written.
   */
  public function sessionWrite($id, $data)
  {
    // get table/column
    $db_table    = $this->options['db_table'];
    $db_data_col = $this->options['db_data_col'];
    $db_id_col   = $this->options['db_id_col'];
    $db_time_col = $this->options['db_time_col'];

    $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.'=?, '.$db_time_col.' = '.time().' WHERE '.$db_id_col.'=?';

    try
    {
      $stmt = $this->con->prepareStatement($sql);
      $stmt->setString(1, $data);
      $stmt->setString(2, $id);
      $stmt->executeUpdate();
    }
    catch (SQLException $e)
    {
      throw new sfDatabaseException(sprintf('Creole SQLException was thrown when trying to manipulate session data. Message: %s.', $e->getMessage()));
    }

    return true;
  }
}
