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
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfRouting
{
  protected
    $dispatcher        = null,
    $cache             = null,
    $defaultParameters = array(),
    $defaultModule     = 'default',
    $defaultAction     = 'index',
    $options           = array();

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, sfCache $cache = null, $options = array())
  {
    $this->initialize($dispatcher, $cache, $options);

    if (isset($this->options['auto_shutdown']) && $this->options['auto_shutdown'])
    {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  /**
   * Initializes this sfRouting instance.
   *
   * Available options:
   *
   *  * default_module: The default module name
   *  * default_action: The default action name
   *  * logging:        Whether to log or not (false by default)
   *  * debug:          Whether to cache or not (false by default)
   *
   * @param sfEventDispatcher A sfEventDispatcher instance
   * @param sfCache           A sfCache instance
   * @param array             An associative array of initialization options.
   */
  public function initialize(sfEventDispatcher $dispatcher, sfCache $cache = null, $options = array())
  {
    $this->dispatcher = $dispatcher;

    $options['debug'] = isset($options['debug']) ? (boolean) $options['debug'] : false;

    // disable caching when in debug mode
    $this->cache = $options['debug'] ? null : $cache;

    if (isset($options['default_module']))
    {
      $this->defaultModule = $options['default_module'];
    }

    if (isset($options['default_action']))
    {
      $this->defaultAction = $options['default_action'];
    }

    if (!isset($options['logging']))
    {
      $options['logging'] = false;
    }

    $this->options = $options;

    $this->dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));
    $this->dispatcher->connect('request.filter_parameters', array($this, 'filterParametersEvent'));
  }

  /**
   * Loads routing configuration.
   *
   * This methods notifies a routing.load_configuration event.
   */
  public function loadConfiguration()
  {
    $this->dispatcher->notify(new sfEvent($this, 'routing.load_configuration'));
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
  *
  * @throws sfError404Exception if the url is not parseable by the sfRouting object
  */
  abstract public function parse($url);

  /**
   * Sets a default parameter.
   *
   * @param string The key
   * @param string The value
   */
  public function setDefaultParameter($key, $value)
  {
    $this->defaultParameters[$key] = $value;
  }

  /**
   * Sets the default parameters for URL generation.
   *
   * @param array An array of default parameters
   */
  public function setDefaultParameters($parameters)
  {
    $this->defaultParameters = $parameters;
  }

  protected function fixDefaults($arr)
  {
    if (empty($arr['module']))
    {
      $arr['module'] = $this->defaultModule;
    }

    if (empty($arr['action']))
    {
      $arr['action'] = $this->defaultAction;
    }

    return $arr;
  }

  protected function mergeArrays($arr1, $arr2)
  {
    foreach ($arr2 as $key => $value)
    {
      $arr1[$key] = $value;
    }

    return $arr1;
  }

  /**
   * Listens to the user.change_culture event.
   *
   * @param sfEvent An sfEvent instance
   *
   */
  public function listenToChangeCultureEvent(sfEvent $event)
  {
    // change the culture in the routing default parameters
    $this->setDefaultParameter('sf_culture', $event['culture']);
  }

  /**
   * Listens to the request.filter_parameters event.
   *
   * @param sfEvent An sfEvent instance
   *
   */
  public function filterParametersEvent(sfEvent $event, $parameters)
  {
    return array_merge($parameters, $this->parse($event['path_info']));
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
  }
}
