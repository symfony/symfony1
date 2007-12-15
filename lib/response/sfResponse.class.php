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
abstract class sfResponse implements Serializable
{
  protected
    $parameterHolder = null,
    $dispatcher      = null,
    $content         = '';

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, $parameters = array())
  {
    $this->initialize($dispatcher, $parameters);
  }

  /**
   * Initializes this sfResponse.
   *
   * @param  sfEventDispatcher  A sfEventDispatcher instance
   * @param  array              An array of parameters
   *
   * @return Boolean            true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfResponse
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array())
  {
    $this->dispatcher = $dispatcher;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Sets the response content
   *
   * @param string Content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * Gets the current response content
   *
   * @return string Content
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Outputs the response content
   */
  public function sendContent()
  {
    $event = $this->dispatcher->filter(new sfEvent($this, 'response.filter_content'), $this->getContent());
    $content = $event->getReturnValue();

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Send content (%s o)', strlen($content)))));
    }

    echo $content;
  }

  /**
   * Sends the content.
   */
  public function send()
  {
    $this->sendContent();
  }

  /**
   * Retrieves the parameters from the current response.
   *
   * @return sfParameterHolder List of parameters
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves a parameter from the current response.
   *
   * @param string A parameter name
   * @param string A default paramter value
   *
   * @return mixed A parameter value
   */
  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Indicates whether or not a parameter exist for the current response.
   *
   * @param string A parameter name
   *
   * @return boolean true, if the parameter exists otherwise false
   */
  public function hasParameter($name)
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets a parameter for the current response.
   *
   * @param string A parameter name
   * @param string The parameter value to be set
   */
  public function setParameter($name, $value)
  {
    $this->parameterHolder->set($name, $value);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string The method name
   * @param array  The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws <b>sfException</b> If the calls fails
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'response.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method sfResponse::%s.', $method));
    }

    return $event->getReturnValue();
  }

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    return serialize(array($this->content, $this->parameterHolder));
  }

  /**
   * Unserializes a sfResponse instance.
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this->initialize(sfContext::hasInstance() ? sfContext::getInstance()->getEventDispatcher() : new sfEventDispatcher());

    list($this->content, $this->parameterHolder) = $data;
  }
}
