<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNoStorage allows you to disable session support.
 *
 * To disable sessions, change the storage factory in config/factories.yml:
 *
 *   storage:
 *    class: sfNoStorage
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfNoStorage extends sfStorage
{
  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws <b>sfStorageException</b> If an error occurs while reading data from this storage
   */
  public function read($key)
  {
    return null;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws <b>sfStorageException</b> If an error occurs while removing data from this storage
   */
  public function remove($key)
  {
    return null;
  }

  /**
   * Executes the shutdown procedure.
   *
   * @throws <b>sfStorageException</b> If an error occurs while shutting down this storage
   */
  public function shutdown()
  {
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   * @param mixed  Data associated with your key
   *
   * @throws <b>sfStorageException</b> If an error occurs while writing to this storage
   */
  public function write($key, $data)
  {
  }
}
