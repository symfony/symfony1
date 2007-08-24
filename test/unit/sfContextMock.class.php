<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfContext
{
  protected static
    $instance = null;

  public
    $request    = null,
    $response   = null,
    $controller = null,
    $routing    = null,
    $user       = null,
    $storage    = null;

  static public function getInstance($factories = array())
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();

      self::$instance->storage = sfStorage::newInstance('sfSessionTestStorage');
      self::$instance->storage->initialize(array('session_path' => sfConfig::get('sf_test_cache_dir').'/sessions'));

      foreach ($factories as $type => $class)
      {
        self::$instance->inject($type, $class);
      }
    }

    return self::$instance;
  }

  public function getModuleName()
  {
    return 'module';
  }

  public function getActionName()
  {
    return 'action';
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getResponse()
  {
    return $this->response;
  }

  public function getRouting()
  {
    return $this->routing;
  }

  public function getStorage()
  {
    return $this->storage;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function getController()
  {
    return $this->controller;
  }

  public function inject($type, $class, $parameters = array())
  {
    $object = new $class();
    if (method_exists($object, 'initialize'))
    {
      in_array($type, array('routing')) ? $object->initialize(null, $parameters) : $object->initialize($this, $parameters);
    }
    $this->$type = $object;
  }
}
