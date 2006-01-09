<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDatabaseManager allows you to setup your database connectivity before the
 * request is handled. This eliminates the need for a filter to manage database
 * connections.
 *
 * @package    symfony
 * @subpackage database
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
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
    require_once(sfConfigCache::checkConfig(sfConfig::get('sf_app_config_dir_name').'/databases.yml'));
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