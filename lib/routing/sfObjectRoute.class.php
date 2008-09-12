<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfObjectRoute represents a route that is bound to PHP object(s).
 *
 * An object route can represent a single object or a list of objects.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfObjectRoute extends sfRequestRoute
{
  /**
   * Constructor.
   *
   * @param string $pattern       The pattern to match
   * @param array  $defaults      An array of default parameter values
   * @param array  $requirements  An array of requirements for parameters (regexes)
   * @param array  $options       An array of options
   *
   * @see sfRoute
   */
  public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
  {
    if (!isset($options['model']))
    {
      throw new InvalidArgumentException('You must pass a "model" option for a sfObjectRoute object.');
    }

    if (!isset($options['object']) && !isset($options['list']))
    {
      throw new InvalidArgumentException('You must pass an "object" or a "list" option for a sfObjectRoute object.');
    }

    parent::__construct($pattern, $defaults, $requirements, $options);
  }

  /**
   * Returns true if the URL matches this route, false otherwise.
   *
   * @param  string  $url     The URL
   * @param  array   $context The context
   *
   * @return array   An array composed of an array of parameters and an array of extra parameters
   */
  public function matchesUrl($url, $context = array())
  {
    if (false === $parameters = parent::matchesUrl($url, $context))
    {
      return false;
    }

    if (isset($this->options['object']))
    {
      // check the related object
      if (is_null($object = $this->getObjectForParameters($parameters[0])))
      {
        throw new sfError404Exception(sprintf('Unable to find the %s object with the following parameters "%s").', $this->options['model'], str_replace("\n", '', var_export($this->filterParameters($parameters[0]), true))));
      }

      return array($parameters[0], array_merge($parameters[1], array($this->options['object'] => false === $object ? null : $object)));
    }
    else
    {
      // object list
      $objects = $this->getObjectsForParameters($parameters[0]);

      if (is_array($objects) && !count($objects) && isset($this->options['allow_empty']) && !$this->options['allow_empty'])
      {
        throw new sfError404Exception(sprintf('No %s object found for the following parameters "%s").', $this->options['model'], str_replace("\n", '', var_export($this->filterParameters($parameters[0]), true))));
      }

      return array($parameters[0], array_merge($parameters[1], array($this->options['list'] => false === $objects ? array() : $objects)));
    }
  }

  /**
   * Returns true if the parameters matches this route, false otherwise.
   *
   * @param  mixed  $params The parameters
   * @param  array  $context The context
   *
   * @return Boolean         true if the parameters matches this route, false otherwise.
   */
  public function matchesParameters($params, $context = array())
  {
    return parent::matchesParameters(isset($this->options['object']) ? $this->convertObjectToArray($params) : $params);
  }

  /**
   * Generates a URL from the given parameters.
   *
   * @param  mixed   $params    The parameter values
   * @param  array   $context   The context
   * @param  Boolean $absolute  Whether to generate an absolute URL
   *
   * @return string The generated URL
   */
  public function generate($params, $context = array(), $absolute = false)
  {
    return parent::generate(isset($this->options['object']) ? $this->convertObjectToArray($params) : $params, $absolute);
  }

  protected function getObjectForParameters($parameters)
  {
    $className = $this->options['model'];

    if (!isset($this->options['method']))
    {
      throw new InvalidArgumentException('You must pass a "method" option for a sfObjectRoute object.');
    }

    return call_user_func(array($className, $this->options['method']), $this->filterParameters($parameters));
  }

  protected function getObjectsForParameters($parameters)
  {
    $className = $this->options['model'];

    if (!isset($this->options['method']))
    {
      throw new InvalidArgumentException('You must pass a "method" option for a sfObjectRoute object.');
    }

    return call_user_func(array($className, $this->options['method']), $this->filterParameters($parameters));
  }

  protected function filterParameters($parameters)
  {
    if (!is_array($parameters))
    {
      return $parameters;
    }

    $params = array();
    foreach (array_keys($this->variables) as $variable)
    {
      $params[$variable] = $parameters[$variable];
    }

    return $params;
  }

  protected function convertObjectToArray($object)
  {
    if (is_array($object))
    {
      if (!isset($object['sf_subject']))
      {
        return $object;
      }

      $parameters = $object;
      $object = $parameters['sf_subject'];
      unset($parameters['sf_subject']);
    }
    else
    {
      $parameters = array();
    }

    return array_merge($parameters, $this->doConvertObjectToArray($object));
  }

  protected function doConvertObjectToArray($object)
  {
    $method = isset($this->options['convert']) ? $this->options['convert'] : 'toParams';

    return $object->$method();
  }

  protected function getRealVariables()
  {
    $variables = array();

    foreach (array_keys($this->variables) as $variable)
    {
      if (0 === strpos($variable, 'sf_'))
      {
        continue;
      }

      $variables[] = $variable;
    }

    return $variables;
  }
}
