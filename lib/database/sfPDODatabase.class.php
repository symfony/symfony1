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
 * sfPDODatabase provides connectivity for the PDO database abstraction layer.
 *
 * @package    symfony
 * @subpackage database
 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfPDODatabase extends sfDatabase
{
  /**
   * Connects to the database.
   *
   * @throws <b>sfDatabaseException</b> If a connection could not be created
   */
  public function connect()
  {
    // determine how to get our parameters
    $method = $this->getParameter('method', 'dsn');

    // get parameters
    switch ($method)
    {
      case 'dsn':
        $dsn = $this->getParameter('dsn');

        if ($dsn == null)
        {
          // missing required dsn parameter
          throw new sfDatabaseException('Database configuration specifies method "dsn", but is missing dsn parameter.');
        }

        break;
    }

    try
    {
      $pdo_username = $this->getParameter('username');
      $pdo_password = $this->getParameter('password');
      $pdo_class    = $this->getParameter('class', 'PDO');

      $this->connection = new $pdo_class($dsn, $pdo_username, $pdo_password);
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException($e->getMessage());
    }

    // lets generate exceptions instead of silent failures
    if (defined('PDO::ATTR_ERRMODE'))
    {
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    else
    {
      $this->connection->setAttribute(PDO_ATTR_ERRMODE, PDO_ERRMODE_EXCEPTION);
    }

    $this->resource = $this->connection;
  }

  /**
   * Executes the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this database
   */
  public function shutdown()
  {
    $this->connection = null;
  }
}
