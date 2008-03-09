<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfParameterHolder provides a base class for managing parameters.
 *
 * Parameters, in this case, are used to extend classes with additional data
 * that requires no additional logic to manage.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfNamespacedParameterHolder implements Serializable
{
  protected $default_namespace = null;
  protected $parameters = array();

  /**
   * The constructor for sfParameterHolder.
   * 
   * The default namespace may be overridden at initialization as follows:
   * <code>
   * <?php
   * $mySpecialPH = new sfParameterHolder('symfony/special');
   * ?>
   * </code>
   */
  public function __construct($namespace = 'symfony/default')
  {
    $this->default_namespace = $namespace;
  }

  /**
   * Sets the default namespace value.
   *
   * @param string  Default namespace
   * @param Boolean Move all values of the old default namespace to the new one or not
   */
  public function setDefaultNamespace($namespace, $move = true)
  {
    if ($move)
    {
      $values = $this->removeNamespace();
      $this->addByRef($values, $namespace);
    }

    $this->default_namespace = $namespace;
  }

  /**
   * Get the default namespace value.
   *
   * The $default_namespace is defined as 'symfony/default'.
   *
   * @return string The default namespace
   */
  public function getDefaultNamespace()
  {
    return $this->default_namespace;
  }

  /**
   * Clear all parameters associated with this request.
   */
  public function clear()
  {
    $this->parameters = null;
    $this->parameters = array();
  }

  /**
   * Retrieve a parameter with an optionally specified namespace.
   *
   * An isolated namespace may be identified by providing a value for the third
   * argument.  If not specified, the default namespace 'symfony/default' is
   * used.
   *
   * @param string A parameter name
   * @param mixed  A default parameter value
   * @param string A parameter namespace
   *
   * @return mixed A parameter value, if the parameter exists, otherwise null
   */
  public function & get($name, $default = null, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns][$name]))
    {
      $value = & $this->parameters[$ns][$name];
    }
    else if (isset($this->parameters[$ns]))
    {
      $value = sfToolkit::getArrayValueForPath($this->parameters[$ns], $name, $default);
    }
    else
    {
      $value = $default;
    }

    return $value;
  }

  /**
   * Retrieve an array of parameter names from an optionally specified namespace.
   *
   * @param string A parameter namespace.
   *
   * @return array An indexed array of parameter names, if the namespace exists, otherwise null
   */
  public function getNames($ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns]))
    {
      return array_keys($this->parameters[$ns]);
    }

    return array();
  }

  /**
   * Retrieve an array of parameter namespaces.
   *
   * @return array An indexed array of parameter namespaces
   */
  public function getNamespaces()
  {
    return array_keys($this->parameters);
  }

  /**
   * Retrieve an array of parameters, within a namespace.
   *
   * This method is limited to a namespace.  Without any argument,
   * it returns the parameters of the default namespace.  If a 
   * namespace is passed as an argument, only the parameters of the
   * specified namespace are returned.
   *
   * @param string A parameter namespace
   *
   * @return array An associative array of parameters
   */
  public function & getAll($ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $parameters = array();

    if (isset($this->parameters[$ns]))
    {
      $parameters = $this->parameters[$ns];
    }

    return $parameters;
  }

  /**
   * Indicates whether or not a parameter exists.
   *
   * @param string A parameter name
   * @param string A parameter namespace
   *
   * @return bool true, if the parameter exists, otherwise false
   */
  public function has($name, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns][$name]))
    {
      return true;
    }
    else if (isset($this->parameters[$ns]))
    {
      return sfToolkit::hasArrayValueForPath($this->parameters[$ns], $name);
    }

    return false;
  }

  /**
   * Indicates whether or not A parameter namespace exists.
   *
   * @param string A parameter namespace
   *
   * @return bool true, if the namespace exists, otherwise false
   */
  public function hasNamespace($ns)
  {
    return isset($this->parameters[$ns]);
  }

  /**
   * Remove a parameter.
   *
   * @param string A parameter name
   * @param mixed  A default parameter value
   * @param string A parameter namespace
   *
   * @return string A parameter value, if the parameter was removed, otherwise null
   */
  public function remove($name, $default = null, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval = $default;

    if (isset($this->parameters[$ns]) && array_key_exists($name, $this->parameters[$ns]))
    {
      $retval = $this->parameters[$ns][$name];
      unset($this->parameters[$ns][$name]);
    }

    return $retval;
  }

  /**
   * Remove A parameter namespace and all of its associated parameters.
   *
   * @param string A parameter namespace.
   */
  public function & removeNamespace($ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval = null;

    if (isset($this->parameters[$ns]))
    {
      $retval =& $this->parameters[$ns];
      unset($this->parameters[$ns]);
    }

    return $retval;
  }

  /**
   * Set a parameter.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string A parameter name
   * @param mixed  A parameter value
   * @param string A parameter namespace
   */
  public function set($name, $value, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] = $value;
  }

  /**
   * Set a parameter by reference.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string A parameter name
   * @param mixed  A reference to a parameter value
   * @param string A parameter namespace
   */
  public function setByRef($name, & $value, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] =& $value;
  }

  /**
   * Set an array of parameters.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array An associative array of parameters and their associated values
   * @param string A parameter namespace
   */
  public function add($parameters, $ns = null)
  {
    if ($parameters === null) return;

    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => $value)
    {
      $this->parameters[$ns][$key] = $value;
    }
  }

  /**
   * Set an array of parameters by reference.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array An associative array of parameters and references to their associated values
   * @param string A parameter namespace
   */
  public function addByRef(& $parameters, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => &$value)
    {
      $this->parameters[$ns][$key] =& $value;
    }
  }

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    return serialize(array($this->default_namespace, $this->parameters));
  }

  /**
   * Unserializes a sfParameterHolder instance.
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this->default_namespace = $data[0];
    $this->parameters = $data[1];
  }
}
