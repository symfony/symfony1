<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfStorage allows you to customize the way symfony stores its persistent data.
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfStorage
{
  private
    $parameter_holder = null,
    $context = null;

  /**
   * Retrieve the current application context.
   *
   * @return sfContext A sfContext instance.
   */
  public function getContext ()
  {
    return $this->context;
  }

  /**
   * Initialize this Storage.
   *
   * @param sfContext A sfContext instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfStorage.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameter_holder = new sfParameterHolder();
    $this->getParameterHolder()->add($parameters);
  }

  /**
   * Retrieve a new Storage implementation instance.
   *
   * @param string A Storage implementation name
   *
   * @return Storage A Storage implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a storage implementation instance cannot be created.
   */
  public static function newInstance ($class)
  {
    // the class exists
    $object = new $class();

    if (!($object instanceof sfStorage))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfStorage';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }

  /**
   * Read data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can
   * be avoided.
   *
   * @param string A unique key identifying your data.
   *
   * @return mixed Data associated with the key.
   *
   * @throws <b>sfStorageException</b> If an error occurs while reading data from this storage.
   */
  abstract function & read ($key);

  /**
   * Remove data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can
   * be avoided.
   *
   * @param string A unique key identifying your data.
   *
   * @return mixed Data associated with the key.
   *
   * @throws <b>sfStorageException</b> If an error occurs while removing data from this storage.
   */
  abstract function & remove ($key);

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfStorageException</b> If an error occurs while shutting down this storage.
   */
  abstract function shutdown ();

  /**
   * Write data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can
   * be avoided.
   *
   * @param string A unique key identifying your data.
   * @param mixed  Data associated with your key.
   *
   * @return void
   *
   * @throws <b>sfStorageException</b> If an error occurs while writing to this storage.
   */
  abstract function write ($key, &$data);
  
  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameter_holder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameter_holder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameter_holder->set($name, $value, $ns);
  }
}

?>