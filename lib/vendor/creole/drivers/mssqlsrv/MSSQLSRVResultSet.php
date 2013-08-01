<?php
/*
 *  $Id: MSSQLSRVResultSet.php 495 2009-01-30 13:40:13Z jupeter $
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

require_once 'creole/ResultSet.php';
require_once 'creole/common/ResultSetCommon.php';

/**
 * MSSQL implementation of ResultSet.
 *
 * MS SQL does not support LIMIT or OFFSET natively so the methods
 * in here need to perform some adjustments and extra checking to make sure
 * that this behaves the same as RDBMS drivers using native OFFSET/LIMIT.
 *
 * @author    Piotr Plenik <piotr.plenik@teamlab.pl>
 * @version   $Revision: 495 $
 * @package   creole.drivers.mssqlsrv
 */
class MSSQLSRVResultSet extends ResultSetCommon implements ResultSet {

  private $fieldNames = null;
  
  /**
   * Offset at which to start reading rows.
   * @var int
   */
  private $offset = 0;
 
  /**
   * Maximum rows to retrieve, or 0 if all.
   * @var int
   */
  private $limit = 0;   
 
  /**
   * This MSSQL-only function exists to set offset after ResultSet is instantiated.
   * This function should be "protected" in Java sense: only available to classes in package.
   * THIS METHOD SHOULD NOT BE CALLED BY ANYTHING EXCEPTION DRIVER CLASSES.
   * @param int $offset New offset.  If great than 0, then seek(0) will be called to move cursor.
   * @access protected
   */
  public function _setOffset($offset)
  {
      $this->offset = $offset;
      if ($offset > 0) {
          $this->seek(0);  // 0 becomes $offset by seek() method
      }
  }
 
  /**
   * This MSSQL-only function exists to set limit after ResultSet is instantiated.
   * This function should be "protected" in Java sense: only available to classes in package.
   * THIS METHOD SHOULD NOT BE CALLED BY ANYTHING EXCEPTION DRIVER CLASSES.
   * @param int $limit New limit.
   * @access protected
   */
  public function _setLimit($limit)
  {
      $this->limit = $limit;
  }

  /**
   * MS SQL driver for PHP doesn't actually move the db pointer.
   *
   * @see ResultSet::seek()
   */
  function seek($rownum)
  {
    if ($rownum < 0) {
      return false;
    }

    if (($this->limit > 0 && $rownum >= $this->limit) || $rownum < 0) {
      // have to check for rownum < 0, because mssql_seek() won't
      // complain if the $actual is valid.
      return false;
    }

    // MSSQL rows start w/ 0, but this works, because we are
    // looking to move the position _before_ the next desired position
    if (!sqlsrv_fetch($this->result, $rownum, $this->offset)) {
        return false;
    }

    $this->cursorPos = $rownum;
    return true;
  }

  /**w
   * @see ResultSet::next()
   */
  function next()
  {
    // support emulated LIMIT
    if ( $this->limit > 0 && ($this->cursorPos >= $this->limit) ) {
        $this->afterLast();
        return false;
    }
    
    $sqlsrvFetchmode = $this->fetchmode === ResultSet::FETCHMODE_ASSOC ? SQLSRV_FETCH_ASSOC :  SQLSRV_FETCH_NUMERIC;
    $this->fields = sqlsrv_fetch_array($this->result, $sqlsrvFetchmode,  SQLSRV_SCROLL_ABSOLUTE, $this->offset + $this->cursorPos);

    if($this->fields == null) // if there are no more results
    {
      $this->afterLast();
      return false;
    }
    elseif($this->fields == false)  // if an error occured
    {
      throw new SQLException("Error fetching result", $this->sqlError() );
    }

    if ($this->fetchmode === ResultSet::FETCHMODE_ASSOC && $this->lowerAssocCase) {
      $this->fields = array_change_key_case($this->fields, CASE_LOWER);
    }

    // Advance cursor position
    $this->cursorPos++;
    return true;
  }

  /**
   * @see ResultSet::getRecordCount()
   */
  function getRecordCount()
  {
    throw new Exception("Function not implemented yet");

    $rows = sqlsrv_fetch_array($this->result);
    if(!$rows)
    {
      throw new SQLException("Error getting record count", $this->sqlError());
    }
    return count($rows);
  }

  public function afterLast()
  {
    return false;
  }

  /**
   * @see ResultSet::close()
   */
  function close()
  {
    sqlsrv_free_stmt($this->result);
    $this->result = false;
    $this->fields = array();
  }

  public function getFieldName($id)
  {
    if($this->fieldNames == null)
    {
      $this->loadFieldNames();
    }

    return $this->fieldNames[$id];
  }

  private function loadFieldNames()
  {
    $metaFields = sqlsrv_field_metadata( $this->result);

    $i = 0;
    foreach($metaFields as $value)
    {
      $this->fieldNames[$i] = $value['Name'];
      $i++;
    }
  }

  private function sqlError()
  {
    return print_r( sqlsrv_errors(), true);
  }

}
