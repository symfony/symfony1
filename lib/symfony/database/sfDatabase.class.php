<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003, 2004 Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+

/**
 * sfDatabase is a base abstraction class that allows you to setup any type of
 * database connection via a configuration file.
 *
 * @package    mojavi
 * @subpackage database
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id: Database.class.php 612 2004-12-07 03:14:53Z seank $
 */
abstract class sfDatabase
{
  protected
    $connection       = null,
    $parameter_holder = null,
    $resource         = null;

  /**
   * Connect to the database.
   *
   * @throws <b>sfDatabaseException</b> If a connection could not be created.
   */
  abstract function connect ();

  /**
   * Retrieve the database connection associated with this sfDatabase implementation.
   *
   * When this is executed on a Database implementation that isn't an
   * abstraction layer, a copy of the resource will be returned.
   *
   * @return mixed A database connection.
   *
   * @throws <b>sfDatabaseException</b> If a connection could not be retrieved.
   */
  public function getConnection ()
  {
    if ($this->connection == null)
      $this->connect();

    return $this->connection;
  }

  /**
   * Retrieve a raw database resource associated with this sfDatabase implementation.
   *
   * @return mixed A database resource.
   *
   * @throws <b>sfDatabaseException</b> If a resource could not be retrieved.
   */
  public function getResource ()
  {
    if ($this->resource == null)
      $this->connect();

    return $this->resource;
  }

  /**
   * Initialize this Database.
   *
   * @param array An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Database.
   */
  public function initialize ($parameters = array())
  {
    $this->parameter_holder = new sfParameterHolder();
    $this->parameter_holder->add($parameters);
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down
   *                                 this database.
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  abstract function shutdown ();
}

?>