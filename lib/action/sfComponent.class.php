<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfComponent.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfComponent
{
  protected
    $context                  = null,
    $var_holder               = null,
    $request                  = null,
    $response                 = null,
    $request_parameter_holder = null;

  /**
   * Execute any application/business logic for this action.
   *
   * In a typical database-driven application, execute() handles application
   * logic itself and then proceeds to create a model instance. Once the model
   * instance is initialized it handles all business logic for the action.
   *
   * A model should represent an entity in your application. This could be a
   * user account, a shopping cart, or even a something as simple as a
   * single product.
   *
   * @return mixed A string containing the view name associated with this action.
   *
   *               Or an array with the following indices:
   *
   *               - The parent module of the view that will be executed.
   *               - The view that will be executed.
   */
  abstract function execute ();

  /**
   * Initialize this action.
   *
   * @param sfContext The current application context.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize($context)
  {
    $this->context                  = $context;
    $this->var_holder               = new sfParameterHolder();
    $this->request                  = $context->getRequest();
    $this->response                 = $context->getResponse();
    $this->request_parameter_holder = $this->request->getParameterHolder();

    return true;
  }

  /**
   * Retrieve the current application context.
   *
   * @return sfContext The current sfContext instance.
   */
  public final function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve the current logger instance.
   *
   * @return sfLogger The current sfLogger instance.
   */
  public final function getLogger ()
  {
    return $this->context->getLogger();
  }

  /**
   * Log $message using sfLogger object.
   * 
   * @param mixed  String or object containing the message to log.
   * @param string The priority of the message
   *               (available priorities: emerg, alert, crit, err, warning, notice, info, debug).
   */
  public function logMessage ($message, $priority = 'info')
  {
    if (sfConfig::get('sf_logging_active'))
    {
      return $this->context->getLogger()->log($message, constant('SF_LOG_'.strtoupper($priority)));
    }
  }

  /**
   * Display $message as a short message in the sfWebDebug toolbar
   *
   * @param string The message text.
   */
  public function debugMessage ($message)
  {
    if (sfConfig::get('sf_web_debug'))
    {
      sfWebDebug::getInstance()->logShortMessage($message);
    }
  }

  /**
   * Returns the value of a request parameter.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->get($name)</code>
   *
   * @param  $name parameter name
   * @return string
   */
  public function getRequestParameter($name, $default = null)
  {
    return $this->request_parameter_holder->get($name, $default);
  }

  /**
   * Returns true if a request parameter exists.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->has($name)</code>
   *
   * @param  $name parameter name
   * @return boolean
   */
  public function hasRequestParameter($name)
  {
    return $this->request_parameter_holder->has($name);
  }

  /**
   * Retrieve the request.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRequest()</code>
   *
   * @return sfRequest The current sfRequest implementation instance.
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Retrieve the response.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getResponse()</code>
   *
   * @return sfResponse The current sfResponse implementation instance.
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Retrieve the Controller.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getController()</code>
   *
   * @return sfController The current sfController implementation instance.
   */
  public function getController()
  {
    return $this->getContext()->getController();
  }

  /**
   * Retrieve the user.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getController()</code>
   *
   * @return sfUser The current sfUser implementation instance.
   */
  public function getUser()
  {
    return $this->getContext()->getUser();
  }

  /**
   * Sets a variable for the template.
   *
   * @param  string The variable name
   * @param  mixed  The variable's value
   * @return void
   */
  public function setVar($name, $value)
  {
    $this->var_holder->set($name, $value);
  }

  /**
   * Gets a variable for the template.
   *
   * @param  string The variable name.
   * @return mixed
   */
  public function getVar($name)
  {
    return $this->var_holder->get($name);
  }

  /**
   * Gets the sfParameterHolder object.
   * 
   * @return sfParameterHolder The variable holder.
   */
  public function getVarHolder()
  {
    return $this->var_holder;
  }

  /**
   * Sets a variable for the template.
   *
   * This is just really a shortcut for:
   * <code>$this->setVar('name', 'value')</code>
   *
   * @param  string key
   * @param  string value
   * @return boolean always true
   */
  public function __set($key, $value)
  {
    return $this->var_holder->setByRef($key, $value);
  }

  /**
   * Gets a variable for the template.
   *
   * This is just really a shortcut for:
   * <code>$this->getVar('name')</code>
   *
   * @param  string key
   * @return mixed
   */
  public function __get($key)
  {
    return $this->var_holder->get($key);
  }

  /**
   * Returns true if a variable for the template is set.
   *
   * This is just really a shortcut for:
   * <code>$this->getVarHolder()->has('name')</code>
   *
   * @param  string key
   * @return boolean
   */
  public function __isset($name)
  {
    return $this->var_holder->has($name);
  }

  /**
   * Removes a variable for the template.
   *
   * This is just really a shortcut for:
   * <code>$this->getVarHolder()->remove('name')</code>
   *
   * @param  string key
   * @return void
   */
  public function __unset($name)
  {
    $this->var_holder->remove($name);
  }

  /**
   * Sets a flash variable that will be passed to the next action.
   *
   * @param  string  name of the flash variable
   * @param  string  value of the flash variable
   * @param  boolean true if the flash have to persist for the following request (true by default)
   * @return void
   */
  public function setFlash($name, $value, $persist = true)
  {
    $this->getUser()->setAttribute($name, $value, 'symfony/flash');

    if ($persist)
    {
      // clear removal flag
      $this->getUser()->getAttributeHolder()->remove($name, 'symfony/flash/remove');
    }
    else
    {
      $this->getUser()->setAttribute($name, true, 'symfony/flash/remove');
    }
  }

  /**
   * Gets a flash variable.
   * 
   * @param  string The name of the flash variable.
   * @return mixed
   */
  public function getFlash($name)
  {
    return $this->getUser()->getAttribute($name, null, 'symfony/flash');
  }

  /**
   * Returns true if a flash variable of the specified name exists.
   * 
   * @param  string The name of the flash variable.
   * @return bool   True if the variable exists, false otherwise.
   */
  public function hasFlash($name)
  {
    return $this->getUser()->hasAttribute($name, 'symfony/flash');
  }

  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfComponent:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfComponent::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
