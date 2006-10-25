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
 * sfRequest provides methods for manipulating client request information such
 * as attributes, errors and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfRequest
{
  /**
   * Process validation and execution for only GET requests.
   *
   */
  const GET = 2;

  /**
   * Skip validation and execution for any request method.
   *
   */
  const NONE = 1;

  /**
   * Process validation and execution for only POST requests.
   *
   */
  const POST = 4;

  protected
    $errors           = array(),
    $context          = null,
    $method           = null,
    $parameter_holder = null,
    $config           = null,
    $attribute_holder = null;

  /**
   * Extract parameter values from the request.
   *
   * @param array An indexed array of parameter names to extract.
   *
   * @return array An associative array of parameters and their values. If
   *               a specified parameter doesn't exist an empty string will
   *               be returned for its value.
   */
  public function & extractParameters ($names)
  {
    $array = array();

    $parameters =& $this->parameter_holder->getAll();
    foreach ($parameters as $key => &$value)
    {
      if (in_array($key, $names))
      {
        $array[$key] =& $value;
      }
    }

    return $array;
  }

  /**
   * Retrieve an error message.
   *
   * @param string An error name.
   *
   * @return string An error message, if the error exists, otherwise null.
   */
  public function getError ($name, $catalogue = 'messages')
  {
    $retval = null;

    if (isset($this->errors[$name]))
    {
      $retval = $this->errors[$name];
    }

    // translate error message if needed
    if (sfConfig::get('sf_i18n'))
    {
      $retval = $this->context->getI18N()->__($retval, null, $catalogue);
    }

    return $retval;
  }

  /**
   * Retrieve an array of error names.
   *
   * @return array An indexed array of error names.
   */
  public function getErrorNames ()
  {
    return array_keys($this->errors);
  }

  /**
   * Retrieve an array of errors.
   *
   * @return array An associative array of errors.
   */
  public function getErrors ()
  {
    return $this->errors;
  }

  /**
   * Retrieve this request's method.
   *
   * @return int One of the following constants:
   *             - sfRequest::GET
   *             - sfRequest::POST
   */
  public function getMethod ()
  {
    return $this->method;
  }

  /**
   * Indicates whether or not an error exists.
   *
   * @param string An error name.
   *
   * @return bool true, if the error exists, otherwise false.
   */
  public function hasError ($name)
  {
    return isset($this->errors[$name]);
  }

  /**
   * Indicates whether or not any errors exist.
   *
   * @return bool true, if any error exist, otherwise false.
   */
  public function hasErrors ()
  {
    return (count($this->errors) > 0);
  }

  /**
   * Initialize this sfRequest.
   *
   * @param Context A sfContext instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;
    $this->parameter_holder->add($parameters);
  }

  public function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve a new Request implementation instance.
   *
   * @param string A Request implementation name.
   *
   * @return Request A Request implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a request implementation instance cannot be created.
   */
  public static function newInstance ($class)
  {
    // the class exists
    $object = new $class();

    // initialize parameter and attribute holders
    $object->parameter_holder = new sfParameterHolder();
    $object->attribute_holder = new sfParameterHolder();

    if (!($object instanceof sfRequest))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfRequest';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }

  /**
   * Remove an error.
   *
   * @param string An error name.
   *
   * @return string An error message, if the error was removed, otherwise null.
   */
  public function & removeError ($name)
  {
    $retval = null;

    if (isset($this->errors[$name]))
    {
      $retval =& $this->errors[$name];

      unset($this->errors[$name]);
    }

    return $retval;
  }

  /**
   * Set an error.
   *
   * @param name    An error name.
   * @param message An error message.
   *
   * @return void
   */
  public function setError ($name, $message)
  {
    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfRequest} error in form for parameter "'.$name.'" (with message "'.$message.'")');

    $this->errors[$name] = $message;
  }

  /**
   * Set an array of errors
   *
   * If an existing error name matches any of the keys in the supplied
   * array, the associated message will be overridden.
   *
   * @param array An associative array of errors and their associated messages.
   *
   * @return void
   */
  public function setErrors ($errors)
  {
    $this->errors = array_merge($this->errors, $errors);
  }

  /**
   * Set the request method.
   *
   * @param int One of the following constants:
   *            - sfRequest::GET
   *            - sfRequest::POST
   *
   * @return void
   *
   * @throws <b>sfException</b> - If the specified request method is invalid.
   */
  public function setMethod ($method)
  {
    if ($method == self::GET || $method == self::POST)
    {
      $this->method = $method;

      return;
    }

    // invalid method type
    $error = 'Invalid request method: %s';
    $error = sprintf($error, $method);

    throw new sfException($error);
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }

  public function getAttributeHolder()
  {
    return $this->attribute_holder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attribute_holder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attribute_holder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attribute_holder->set($name, $value, $ns);
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
    if (!$callable = sfMixer::getCallable('sfRequest:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfRequest::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
