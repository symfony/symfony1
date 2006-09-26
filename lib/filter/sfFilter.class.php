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
 * sfFilter provides a way for you to intercept incoming requests or outgoing
 * responses.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfFilter
{
  protected
    $parameter_holder = null,
    $filterCalled     = array(),
    $context          = null;

  protected function isFirstCallBeforeExecution ()
  {
    return $this->isFirstCall('beforeExecution');
  }

  protected function isFirstCallBeforeRendering ()
  {
    return $this->isFirstCall('beforeRendering');
  }

  protected function isFirstCall ($type = 'beforeExecution')
  {
    $class = get_class($this);
    if (isset($this->filterCalled[$class][$type]))
    {
      return false;
    }
    else
    {
      $this->filterCalled[$class][$type] = true;

      return true;
    }
  }

  /**
   * Retrieve the current application context.
   *
   * @return Context The current Context instance.
   */
  public final function getContext ()
  {
    return $this->context;
  }

  /**
   * Initialize this Filter.
   *
   * @param Context The current application context.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Filter.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameter_holder = new sfParameterHolder();
    $this->parameter_holder->add($parameters);

    return true;
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
