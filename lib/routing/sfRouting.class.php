<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRouting class controls the generation and parsing of URLs.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfRouting
{
  protected
    $context         = null,
    $parameterHolder = null;

  /**
   * Retrieves a new sfRouting implementation instance.
   *
   * @param string A sfRouting implementation name
   *
   * @return sfRouting A sfRouting implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a routing implementation instance cannot
   */
  public static function newInstance($class)
  {
    // the class exists
    $object = new $class();

    if (!$object instanceof sfRouting)
    {
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfRouting.', $class));
    }

    return $object;
  }

  /**
   * Initializes this sfRouting instance.
   *
   * @param sfContext A sfContext instance.
   * @param array     An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this User.
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Gets the internal URI for the current request.
   *
   * @param boolean Whether to give an internal URI with the route name (@route)
   *                or with the module/action pair
   *
   * @return string The current internal URI
   */
  abstract public function getCurrentInternalUri($with_route_name = false);

  /**
   * Gets the current compiled route array.
   *
   * @return array The route array
   */
  abstract public function getRoutes();

  /**
   * Sets the compiled route array.
   *
   * @param array The route array
   *
   * @return array The route array
   */
  abstract public function setRoutes($routes);

  /**
   * Returns true if this instance has some routes.
   *
   * @return  boolean
   */
  abstract public function hasRoutes();

  /**
   * Clears all current routes.
   */
  abstract public function clearRoutes();

 /**
  * Generates a valid URLs for parameters.
  *
  * @param  array  The parameter values
  * @param  string The divider between key/value pairs
  * @param  string The equal sign to use between key and value
  *
  * @return string The generated URL
  */
  abstract public function generate($name, $params, $querydiv = '/', $divider = '/', $equals = '/');

 /**
  * Parses a URL to find a matching route.
  *
  * Returns null if no route match the URL.
  *
  * @param  string URL to be parsed
  *
  * @return array  An array of parameters
  */
  abstract public function parse($url);

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
  }
}
