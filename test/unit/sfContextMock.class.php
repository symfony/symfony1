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
  private static $instance = null;

  public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();
    }

    return self::$instance;
  }

  public function getModuleName()
  {
    return '';
  }

  public function getRequest()
  {
    $request = new sfWebRequest();
    $request->initialize($this);

    return $request;
  }

  public function getResponse()
  {
    $response = new sfWebResponse();
    $response->initialize($this);

    return $response;
  }

  public function getStorage()
  {
    $storage = sfStorage::newInstance('sfSessionTestStorage');
    $storage->initialize($this);

    return $storage;
  }
}
