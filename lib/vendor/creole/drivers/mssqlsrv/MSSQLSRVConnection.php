<?php

/*
 *  $Id: MSSQLSRVConnection.php 502 2009-01-30 15:28:05Z jupeter $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */


require_once 'creole/Connection.php';
require_once 'creole/common/ConnectionCommon.php';
include_once 'creole/drivers/mssqlsrv/MSSQLSRVResultSet.php';

/**
 * @author    Piotr PLenik <piotr.plenik@teamlab.pl>
 * @version   $Revision: 502 $
 * @package   creole.drivers.mssqlsrv
 */
class MSSQLSRVConnection extends ConnectionCommon implements Connection {

  /** Current database (used in mssql_select_db()). */
  private $database;

  /** LastStmt used to count last update SQL **/
  private $lastStmt = null;

  /**
   * @see Connection::connect()
   */
  function connect($dsninfo, $flags = 0)
  {
    if (!extension_loaded('sqlsrv')) {
      throw new SQLException('sqlsrv extension not loaded');
    }

    $this->dsn = $dsninfo;
    $this->flags = $flags;

    $serverName = $dsninfo['hostspec'] ? $dsninfo['hostspec'] : '(local)';

    if(!empty($dsninfo['port'])) {
      throw new SQLException('dsninfo[\'port\'] not implemented');
    }

    $connectionInfo = array();
    if(array_key_exists('username', $dsninfo) && $dsninfo['username'] != '')
    {
      $connectionInfo['UID'] = $dsninfo['username'];
    }
    if(array_key_exists('password', $dsninfo) && $dsninfo['password'] != '')
    {
      $connectionInfo['PWD'] = $dsninfo['password'];
    }
    if(array_key_exists('database', $dsninfo))
    {
      $connectionInfo['Database'] = $dsninfo['database'];
    }

    $conn = sqlsrv_connect( $serverName, $connectionInfo);
    if( $conn === false )
    {
      throw new SQLException('connect failed', $this->sqlError());
    }

    $this->dblink = $conn;
  }

  /**
   * @see Connection::getDatabaseInfo()
   */
  public function getDatabaseInfo()
  {
    require_once 'creole/drivers/mssqlsrv/metadata/MSSQLSRVDatabaseInfo.php';
    return new MSSQLSRVDatabaseInfo($this);
  }

  /**
   * @see Connection::getIdGenerator()
   */
  public function getIdGenerator()
  {
    require_once 'creole/drivers/mssqlsrv/MSSQLSRVIdGenerator.php';
    return new MSSQLSRVIdGenerator($this);
  }

  /**
   * @see Connection::prepareStatement()
   */
  public function prepareStatement($sql)
  {
    require_once 'creole/drivers/mssqlsrv/MSSQLSRVPreparedStatement.php';
    return new MSSQLSRVPreparedStatement($this, $sql);
  }

  /**
   * @see Connection::createStatement()
   */
  public function createStatement()
  {
    require_once 'creole/drivers/mssql/MSSQLStatement.php';
    return new MSSQLSRVStatement($this);
  }

  /**
   * Returns false since MSSQL doesn't support this method.
   */
  public function applyLimit(&$sql, $offset, $limit)
  {
    return false;
  }

  /**
   * @see Connection::close()
   */
  function close()
  {
    $ret = sqlsrv_close( $this->dblink);
    $this->dblink = null;
    return $ret;
  }

  /**
   * @see Connection::executeQuery()
   */
  function executeQuery($sql, $fetchmode = null)
  {
    $this->lastQuery = $sql;
    
    $result = sqlsrv_query($this->dblink, $sql, null, array("Scrollable" => SQLSRV_CURSOR_STATIC));    
    if($result === false)
    {
      throw new SQLException('Could not execute query: ' . $sql,  $this->sqlError());
    }

    // get first results with has fields
    $numfields = sqlsrv_num_fields( $result );
    while(($numfields == false)&&(sqlsrv_num_fields( $result )))
    {
      $numfields = sqlsrv_fetch_array( $result );
    }

    return new MSSQLSRVResultSet($this, $result, $fetchmode);
  }

  /**
   * @see Connection::executeUpdate()
   */
  function executeUpdate($sql)
  {
    $this->lastQuery = $sql;

    $stmt = sqlsrv_query( $this->dblink, $sql);
    
    if (!$stmt) {
        throw new SQLException('Could not execute update', $this->sqlError(), $sql);
    }

    $rows_affected = sqlsrv_rows_affected( $stmt);
    if( $rows_affected === false)
    {
      throw new SQLException('Error in calling sqlsrv_rows_affected',  $this->sqlError());
    }

    $this->lastStmt = $stmt;  // set to getUpdateCount() method

    return $this->getUpdateCount();
  }

  /**
   * Start a database transaction.
   * @throws SQLException
   * @return void
   */
  protected function beginTrans()
  {
    return;
    $result = sqlsrv_begin_transaction( $this->dblink );
    if ( $result === false )
    {
      throw new SQLException('Could not begin transaction', $this->sqlError());
    }
  }

  /**
   * Commit the current transaction.
   * @throws SQLException
   * @return void
   */
  protected function commitTrans()
  {
    return;
    $result = sqlsrv_commit( $this->dblink );
    if (!$result) {
      throw new SQLException('Could not commit transaction', $this->sqlError());
    }
  }

  /**
   * Roll back (undo) the current transaction.
   * @throws SQLException
   * @return void
   */
  protected function rollbackTrans()
  {
    return;
    $result = sqlsrv_rollback( $this->dblink );
    if (!$result) {
      throw new SQLException('Could not rollback transaction', $this->sqlError());
    }
  }

  /**
   * Gets the number of rows affected by the last query.
   * if the last query was a select, returns 0.
   *
   * @return int Number of rows affected by the last query
   * @throws SQLException
   */
  function getUpdateCount()
  {
    $rowsCount = sqlsrv_rows_affected($this->lastStmt);
    $rowsCount = 1;
    if($rowsCount === false)
    {
      throw new SQLException('Unable to get affected row count', $this->sqlError());
    }

    if($rowsCount == -1)
    {
      return 0;
    }

    return $rowsCount;
  }


  /**
   * Creates a CallableStatement object for calling database stored procedures.
   *
   * @param string $sql
   * @return CallableStatement
   * @throws SQLException
   */
  function prepareCall($sql)
  {
    require_once 'creole/drivers/mssqlsrv/MSSQLSRVCallableStatement.php';
    $stmt = sqlsrv_prepare($sql);
    if ($stmt === false) {
      throw new SQLException('Unable to prepare statement', $this->sqlError(), $sql);
    }
    return new MSSQLSRVCallableStatement($this, $stmt);
  }

  private function sqlError()
  {
    return print_r( sqlsrv_errors(), true);
  }
  
  /**
   * returns the last inserted id
   * 
   * @return int
   * @throws SQLException
   */
  function getLastInsertedId()
  { 
    if (
        (sqlsrv_next_result($this->lastStmt) !== true) || 
        (sqlsrv_fetch($this->lastStmt) !== true) ||
        (($lastInsertedId = sqlsrv_get_field($this->lastStmt, 0)) === false)
       ) {
      throw new SQLException('Unable to retrieve last inserted id', $this->sqlError());
    }
    
    return $lastInsertedId;
  }
}