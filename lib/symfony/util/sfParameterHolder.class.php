<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfParameterHolder provides a base class for managing parameters.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfView.class.php 422 2005-09-03 16:11:31Z fabien $
 */
class sfParameterHolder
{
  protected $default_namespace = null;
  protected $parameters = array();

  public function __construct($namespace = 'symfony/default')
  {
    $this->default_namespace = $namespace;
  }

  public function getDefaultNamespace ()
  {
    return $this->default_namespace;
  }

  /**
   * Clear all parameters associated with this request.
   *
   * @return void
   */
  public function clear ()
  {
    $this->parameters = null;
    $this->parameters = array();
  }

  /**
   * Retrieve a parameter.
   *
   * @param string A parameter name.
   * @param mixed  A default parameter value.
   * @param string A parameter namespace.
   *
   * @return mixed A parameter value, if the parameter exists, otherwise null.
   */
  public function & get ($name, $default = null, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval =& $default;

    if (isset($this->parameters[$ns]) && isset($this->parameters[$ns][$name]))
    {
      $retval = $this->parameters[$ns][$name];
    }

    return $retval;
  }

  /**
   * Retrieve an array of parameter names.
   *
   * @param string A parameter namespace.
   *
   * @return array An indexed array of parameter names, if the namespace exists, otherwise null.
   */
  public function getNames ($ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns]))
    {
      return array_keys($this->parameters[$ns]);
    }

    return null;
  }

  /**
   * Retrieve an array of parameter namespaces.
   *
   * @return array An indexed array of parameter namespaces.
   */
  public function getNamespaces ()
  {
    return array_keys($this->parameters);
  }

  /**
   * Retrieve an array of parameters.
   *
   * @param string A parameter namespace.
   *
   * @return array An associative array of parameters.
   */
  public function & getAll ($ns = null)
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
   * @param string A parameter name.
   * @param string A parameter namespace.
   *
   * @return bool true, if the parameter exists, otherwise false.
   */
  public function has ($name, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns]))
    {
      return isset($this->parameters[$ns][$name]);
    }

    return false;
  }

  /**
   * Indicates whether or not A parameter namespace exists.
   *
   * @param string A parameter namespace.
   *
   * @return bool true, if the namespace exists, otherwise false.
   */
  public function hasNamespace ($ns)
  {
    return isset($this->parameters[$ns]);
  }

  /**
   * Remove a parameter.
   *
   * @param string A parameter name.
   * @param string A parameter namespace.
   *
   * @return string A parameter value, if the parameter was removed, otherwise null.
   */
  public function & remove ($name, $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval = null;

    if (isset($this->parameters[$ns]) && isset($this->parameters[$ns][$name]))
    {
      $retval =& $this->parameters[$ns][$name];
      unset($this->parameters[$ns][$name]);
    }

    return $retval;
  }

  /**
   * Remove A parameter namespace and all of its associated parameters.
   *
   * @param string A parameter namespace.
   *
   * @return void
   */
  public function & removeNamespace ($ns)
  {
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
   * @param string A parameter name.
   * @param mixed  A parameter value.
   * @param string A parameter namespace.
   *
   * @return void
   */
  public function set ($name, $value, $ns = null)
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
   * @param string A parameter name.
   * @param mixed  A reference to a parameter value.
   * @param string A parameter namespace.
   *
   * @return void
   */
  public function setByRef ($name, & $value, $ns = null)
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
   * @param array An associative array of parameters and their associated values.
   * @param string A parameter namespace.
   *
   * @return void
   */
  public function add ($parameters, $ns = null)
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
   * @param array An associative array of parameters and references to their associated values.
   * @param string A parameter namespace.
   *
   * @return void
   */
  public function addByRef (& $parameters, $ns = null)
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
}

?>