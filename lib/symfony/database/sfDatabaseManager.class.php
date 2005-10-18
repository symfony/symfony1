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
 * sfDatabaseManager allows you to setup your database connectivity before the
 * request is handled. This eliminates the need for a filter to manage database
 * connections.
 *
 * @package    mojavi
 * @subpackage database
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id: DatabaseManager.class.php 580 2004-12-06 03:15:21Z seank $
 */
class sfDatabaseManager
{
  private
    $databases = array();

  /**
   * Retrieve the database connection associated with this Database
   * implementation.
   *
   * @param string A database name.
   *
   * @return mixed A Database instance.
   *
   * @throws <b>sfDatabaseException</b> If the requested database name does not exist.
   */
  public function getDatabase ($name = 'default')
  {
    if (isset($this->databases[$name]))
      return $this->databases[$name];

    // nonexistent database name
    $error = 'Database "%s" does not exist';
    $error = sprintf($error, $name);

    throw new sfDatabaseException($error);
  }

  /**
   * Initialize this DatabaseManager.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this DatabaseManager.
   */
  public function initialize ()
  {
    // load database configuration
    require_once(sfConfigCache::checkConfig(SF_APP_CONFIG_DIR_NAME.'/databases.yml'));
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this DatabaseManager.
   */
  public function shutdown ()
  {
    // loop through databases and shutdown connections
    foreach ($this->databases as $database)
      $database->shutdown();
  }
}

?>