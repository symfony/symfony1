<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfRegistry.class.php 438 2005-09-12 06:31:12Z fabien $
 */
class sfRegistry
{
  protected static
    $instance = false;

  protected
    $parameter_holder = null;

  private function __construct()
  {
  }

  public static function getInstance()
  {
    if (self::$instance === false)
    {
      self::$instance = new sfRegistry();
      self::$instance->parameter_holder = new sfParameterHolder();
    }

    return self::$instance;
  }

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