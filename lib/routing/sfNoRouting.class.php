<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNoRouting class is a very simple routing class that uses GET parameters.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfNoRouting extends sfRouting
{
  /**
   * Gets the internal URI for the current request.
   *
   * @param boolean Whether to give an internal URI with the route name (@route)
   *                or with the module/action pair
   *
   * @return string The current internal URI
   */
  public function getCurrentInternalUri($with_route_name = false)
  {
    $parameters = $_GET;

    // module/action
    $module = isset($parameters['module']) ? $parameters['module'] : $this->options['default_module'];
    $action = isset($parameters['action']) ? $parameters['action'] : $this->options['default_action'];

    // other parameters
    unset($parameters['module'], $parameters['action']);
    ksort($parameters);
    $parameters = count($parameters) ? '?'.http_build_query($parameters, null, '&') : '';

    return sprintf('%s/%s%s', $module, $action, $parameters);
  }

 /**
  * Generates a valid URLs for parameters.
  *
  * @param  array  The parameter values
  * @param  string The divider between key/value pairs
  * @param  string The equal sign to use between key and value
  *
  * @return string The generated URL
  */
  public function generate($name, $params, $querydiv = '/', $divider = '/', $equals = '/')
  {
    $parameters = http_build_query(array_merge($this->defaultParameters, $params), null, '&');

    return '/'.($parameters ? '?'.$parameters : '');
  }

 /**
  * Parses a URL to find a matching route.
  *
  * Returns null if no route match the URL.
  *
  * @param  string URL to be parsed
  *
  * @return array  An array of parameters
  */
  public function parse($url)
  {
    return array();
  }

  /**
   * Gets the current compiled route array.
   *
   * @return array The route array
   */
  public function getRoutes()
  {
    return array();
  }

  /**
   * Sets the compiled route array.
   *
   * @param array The route array
   *
   * @return array The route array
   */
  public function setRoutes($routes)
  {
    return array();
  }

  /**
   * Returns true if this instance has some routes.
   *
   * @return  boolean
   */
  public function hasRoutes()
  {
    return false;
  }

  /**
   * Clears all current routes.
   */
  public function clearRoutes()
  {
  }
}
