<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfResponse provides methods for manipulating client response information such
 * as headers, cookies and content.
 *
 * @package    symfony
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfResponse
{
  protected
    $parameter_holder = null,
    $context = null,
    $content = '';

  /**
   * Initialize this sfResponse.
   *
   * @param sfContext A sfContext instance.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Response.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameter_holder = new sfParameterHolder();
    $this->parameter_holder->add($parameters);
  }

  public function setContext ($context)
  {
    $this->context = $context;
  }

  public function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve a new sfResponse implementation instance.
   *
   * @param string A sfResponse implementation name.
   *
   * @return sfResponse A sfResponse implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a request implementation instance cannot be created.
   */
  public static function newInstance ($class)
  {
    // the class exists
    $object = new $class();

    if (!($object instanceof sfResponse))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfResponse';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }

  /**
   * Set the response content
   *
   * @param string content
   */
  public function setContent ($content)
  {
    $this->content = $content;
  }

  /**
   * Get the current response content
   *
   * @return string
   */
  public function getContent ()
  {
    return $this->content;
  }

  /**
   * Outputs the response content
   */
  public function sendContent ()
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfResponse} send content ('.strlen($this->content).' o)');
    }

    echo $this->content;
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

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  abstract function shutdown ();

  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfResponse:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfResponse::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
