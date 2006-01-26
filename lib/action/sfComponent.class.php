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
    return $this->context->getLogger()->log($message, constant('SF_PEAR_LOG_'.strtoupper($priority)));
  }

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
   * Returns the current request object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRequest()</code>
   *
   * @return object current request object
   */
  public function getRequest()
  {
    return $this->request;
  }

  public function getController()
  {
    return $this->getContext()->getController();
  }

  public function getUser()
  {
    return $this->getContext()->getUser();
  }

  public function setVar($name, $value)
  {
    $this->var_holder->set($name, $value);
  }

  public function getVar($name)
  {
    return $this->var_holder->get($name);
  }

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

  public function setFlash($name, $value, $persist = true)
  {
    $this->getUser()->setAttribute($name, $value, 'symfony/flash');

    if (!$persist)
    {
      $this->getUser()->setAttribute($name, true, 'symfony/flash/remove');
    }
  }

  public function getFlash($name)
  {
    return $this->getUser()->getAttribute($name, null, 'symfony/flash');
  }

  public function hasFlash($name)
  {
    return $this->getUser()->hasAttribute($name, 'symfony/flash');
  }
}

?>