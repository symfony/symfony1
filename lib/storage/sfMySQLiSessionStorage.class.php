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
 * Provides support for session storage using a MySQL brand database 
 * using the MySQL improved API.
 *
 * <b>parameters:</b> see sfDatabaseSessionStorage
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @author     Julien Garand <julien.garand@gmail.com>
 * @version    SVN: $Id: sfMySQLSessionStorage.class.php 8506 2008-04-17 15:56:05Z fabien $
 */
class sfMySQLiSessionStorage extends sfMySQLSessionStorage
{
  /*!
   * Execute an SQL Query
   *
   * @param $query (string) The query to execute
   * @return (mixed) The result of the query
   */
  protected function db_query($query)
  {
    return $this->db->getResource()->query($query);
  }

  /*!
   * Escape a string before using it in a query statement
   *
   * @param $string (string) The string to escape
   * @return (string) The escaped string
   */
  protected function db_escape($string)
  {
    return $this->db->getResource()->real_escape_string($string);
  }

  /*!
   * Count the rows in a query result
   *
   * @param $result (resource) Result of a query
   * @return (int) Number of rows
   */
  protected function db_num_rows($result)
  {
    return $result->num_rows;
  }

  /*!
   * Extract a row from a query result set
   *
   * @param $result (resource) Result of a query
   * @return (array) Extracted row as an indexed array
   */
  protected function db_fetch_row($result)
  {
    return $result->fetch_row();
  }
}
