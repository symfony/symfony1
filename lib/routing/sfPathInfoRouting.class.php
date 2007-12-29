<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPathInfoRouting class is a very simple routing class that uses PATH_INFO.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPathInfoRouting extends sfRouting
{
  protected
    $currentRouteParameters = array();

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
    $parameters = $this->currentRouteParameters;
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
    $url = '';
    $params = array_merge($this->defaultParameters, $params);
    foreach ($params as $key => $value)
    {
      $url .= '/'.$key.'/'.$value;
    }

    return $url ? $url : '/';
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
    $this->currentRouteParameters = array();
    $array = explode('/', trim($url, '/'));
    $count = count($array);

    for ($i = 0; $i < $count; $i++)
    {
      // see if there's a value associated with this parameter, if not we're done with path data
      if ($count > ($i + 1))
      {
        $this->currentRouteParameters[$array[$i]] = $array[++$i];
      }
    }

    return $this->currentRouteParameters;
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
