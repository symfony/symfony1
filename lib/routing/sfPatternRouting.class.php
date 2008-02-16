<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPatternRouting class controls the generation and parsing of URLs.
 *
 * It maps an array of parameters to URLs definition. Each map is called a route.
 *
 * This class was initialy based on the Routes class of the Cake framework.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPatternRouting extends sfRouting
{
  protected
    $currentRouteName       = null,
    $currentInternalUri     = array(),
    $currentRouteParameters = null,
    $defaultSuffix          = '',
    $routes                 = array();

  /**
   * @see sfRouting
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    parent::initialize($dispatcher, $options);

    $this->setDefaultSuffix(isset($options['suffix']) ? $options['suffix'] : '');
  }

  /**
   * @see sfRouting
   */
  public function loadConfiguration()
  {
    if ($config = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/routing.yml', true))
    {
      include($config);
    }

    parent::loadConfiguration();
  }

  /**
   * Gets the internal URI for the current request.
   *
   * @param boolean Whether to give an internal URI with the route name (@route)
   *                or with the module/action pair
   *
   * @return string The current internal URI
   */

  public function getCurrentInternalUri($withRouteName = false)
  {
    if (is_null($this->currentRouteName))
    {
      return null;
    }

    $typeId = ($withRouteName) ? 0 : 1;

    if (!isset($this->currentInternalUri[$typeId]))
    {
      $parameters = $this->currentRouteParameters;

      list($url, $regexp, $names, $namesHash, $defaults, $requirements, $suffix) = $this->routes[$this->currentRouteName];

      if ($withRouteName)
      {
        $internalUri = '@'.$this->currentRouteName;
      }
      else
      {
        $module = isset($parameters['module']) && $parameters['module'] ? $parameters['module'] : $this->options['default_module'];
        $action = isset($parameters['action']) && $parameters['action'] ? $parameters['action'] : $this->options['default_action'];

        $internalUri = $module.'/'.$action;
      }

      $params = array();

      // add parameters
      foreach ($names as $name)
      {
        if ($name == 'module' || $name == 'action')
        {
          continue;
        }

        $params[] = $name.'='.(isset($parameters[$name]) ? $parameters[$name] : (isset($defaults[$name]) ? $defaults[$name] : ''));
      }

      // add * parameters if needed
      if (strpos($url, '*'))
      {
        foreach ($parameters as $key => $value)
        {
          if ($key == 'module' || $key == 'action' || in_array($key, $names))
          {
            continue;
          }

          $params[] = $key.'='.$value;
        }
      }

      // sort to guaranty unicity
      sort($params);

      $this->currentInternalUri[$typeId] = $internalUri.($params ? '?'.implode('&', $params) : '');
    }

    return $this->currentInternalUri[$typeId];
  }

  /**
   * Sets the default suffix
   *
   * @param string The default suffix
   */
  public function setDefaultSuffix($suffix)
  {
    $this->defaultSuffix = '.' == $suffix ? '' : $suffix;
  }

  /**
   * Gets the current compiled route array.
   *
   * @return array The route array
   */
  public function getRoutes()
  {
    return $this->routes;
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
    return $this->routes = $routes;
  }

  /**
   * Returns true if this instance has some routes.
   *
   * @return  boolean
   */
  public function hasRoutes()
  {
    return count($this->routes) ? true : false;
  }

  /**
   * Clears all current routes.
   */
  public function clearRoutes()
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array('Clear all current routes')));
    }

    $this->routes = array();
  }

  /**
   * Returns true if the route name given is defined.
   *
   * @param string The route name
   *
   * @return  boolean
   */
  public function hasRouteName($name)
  {
    return isset($this->routes[$name]) ? true : false;
  }

  /**
   * Adds a new route at the beginning of the current list of routes.
   *
   * @see connect
   */
  public function prependRoute($name, $route, $default = array(), $requirements = array())
  {
    $routes = $this->routes;
    $this->routes = array();
    $newroutes = $this->connect($name, $route, $default, $requirements);
    $this->routes = array_merge($newroutes, $routes);

    return $this->routes;
  }

  /**
   * Adds a new route.
   *
   * Alias for the connect method.
   *
   * @see connect
   */
  public function appendRoute($name, $route, $default = array(), $requirements = array())
  {
    return $this->connect($name, $route, $default, $requirements);
  }

  /**
   * Adds a new route at the end of the current list of routes.
   *
   * A route string is a string with 2 special constructions:
   * - :string: :string denotes a named paramater (available later as $request->getParameter('string'))
   * - *: * match an indefinite number of parameters in a route
   *
   * Here is a very common rule in a symfony project:
   *
   * <code>
   * $r->connect('/:module/:action/*');
   * </code>
   *
   * @param  string The route name
   * @param  string The route string
   * @param  array  The default parameter values
   * @param  array  The regexps parameters must match
   *
   * @return array  current routes
   */
  public function connect($name, $route, $default = array(), $requirements = array())
  {
    // route already exists?
    if (isset($this->routes[$name]))
    {
      throw new sfConfigurationException(sprintf('This named route already exists ("%s").', $name));
    }

    $parsed = array();
    $names  = array();
    $suffix = $this->defaultSuffix;

    // a route must start by a slash. If there is none, add it automatically
    if ('/' != $route[0])
    {
      $route = '/'.$route; 
    }

    if ($route == '/')
    {
      $this->routes[$name] = array($route, '/^[\/]*$/', array(), array(), $default, $requirements, $suffix);
    }
    else
    {
      // used for performance reasons
      $namesHash = array();
      $r = null;
      $elements = array();
      foreach (explode('/', $route) as $element)
      {
        if (trim($element))
        {
          $elements[] = $element;
        }
      }

      if (!isset($elements[0]))
      {
        return false;
      }

      // specific suffix for this route?
      // or /$ directory
      if (preg_match('/^(.+)(\.\w*)$/i', $elements[count($elements) - 1], $matches))
      {
        $suffix = '.' == $matches[2] ? '' : $matches[2];
        $elements[count($elements) - 1] = $matches[1];
        $route = '/'.implode('/', $elements);
      }
      else if ($route{strlen($route) - 1} == '/')
      {
        $suffix = '/';
      }

      $regexp_suffix = preg_quote($suffix);

      foreach ($elements as $element)
      {
        if (preg_match('/^:(.+)$/', $element, $r))
        {
          $element = $r[1];

          // regex is [^\/]+ or the requirement regex
          if (isset($requirements[$element]))
          {
            $regex = $requirements[$element];
            if (0 === strpos($regex, '^'))
            {
              $regex = substr($regex, 1);
            }
            if (strlen($regex) - 1 === strpos($regex, '$'))
            {
              $regex = substr($regex, 0, -1);
            }
          }
          else
          {
            $regex = '[^\/]+';
          }

          $parsed[] = '(?:\/('.$regex.'))?';
          $names[] = $element;
          $namesHash[$element] = 1;
        }
        elseif (preg_match('/^\*$/', $element, $r))
        {
          $parsed[] = '(?:\/(.*))?';
        }
        else
        {
          $parsed[] = '/'.$element;
        }
      }
      $regexp = '#^'.join('', $parsed).$regexp_suffix.'$#';

      $this->routes[$name] = array($route, $regexp, $names, $namesHash, $default, $requirements, $suffix);
    }

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Connect "%s"%s', $route, $suffix ? ' ("'.$suffix.'" suffix)' : ''))));
    }

    return $this->routes;
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
    // named route?
    if ($name)
    {
      if (!isset($this->routes[$name]))
      {
        throw new sfConfigurationException(sprintf('The route "%s" does not exist.', $name));
      }

      list($url, $regexp, $names, $namesHash, $defaults, $requirements, $suffix) = $this->routes[$name];
      $defaults = array_merge($defaults, $this->defaultParameters);

      // all params must be given
      foreach ($names as $tmp)
      {
        if (!isset($params[$tmp]) && !isset($defaults[$tmp]))
        {
          throw new sfException(sprintf('Route named "%s" have a mandatory "%s" parameter.', $name, $tmp));
        }
      }
    }
    else
    {
      // find a matching route
      $found = false;
      foreach ($this->routes as $name => $route)
      {
        list($url, $regexp, $names, $namesHash, $defaults, $requirements, $suffix) = $route;
        $defaults = array_merge($defaults, $this->defaultParameters);

        $tparams = array_merge($defaults, $params);

        // we must match all names (all $names keys must be in $params array)
        foreach ($names as $key)
        {
          if (!isset($tparams[$key])) continue 2;
        }

        // we must match all defaults with value except if present in names
        foreach ($defaults as $key => $value)
        {
          if (isset($namesHash[$key])) continue;

          if (!isset($tparams[$key]) || $tparams[$key] != $value) continue 2;
        }

        // we must match all requirements for rule
        foreach ($requirements as $req_param => $req_regexp)
        {
          if (!preg_match('/'.str_replace('/', '\\/', $req_regexp).'/', $tparams[$req_param]))
          {
            continue 2;
          }
        }

        // we must have consumed all $params keys if there is no * in route
        if (!strpos($url, '*'))
        {
          if (count(array_diff(array_keys($tparams), $names, array_keys($defaults))))
          {
            continue;
          }
        }

        // match found
        $found = true;
        break;
      }

      if (!$found)
      {
        throw new sfConfigurationException(sprintf('Unable to find a matching routing rule to generate url for params "%s".', var_export($params, true)));
      }
    }

    $params = sfToolkit::arrayDeepMerge($defaults, $params);

    $realUrl = preg_replace('/\:([^\/]+)/e', 'urlencode($params["\\1"])', $url);

    // we add all other params if *
    if (strpos($realUrl, '*'))
    {
      $tmp = array();
      foreach ($params as $key => $value)
      {
        if (isset($namesHash[$key]) || isset($defaults[$key])) continue;

        if (is_array($value))
        {
          foreach ($value as $v)
          {
            $tmp[] = $key.$equals.urlencode($v);
          }
        }
        else
        {
          $tmp[] = urlencode($key).$equals.urlencode($value);
        }
      }
      $tmp = implode($divider, $tmp);
      if (strlen($tmp) > 0)
      {
        $tmp = $querydiv.$tmp;
      }
      $realUrl = preg_replace('/\/\*(\/|$)/', "$tmp$1", $realUrl);

      // strip off last divider character
      if (strlen($realUrl) > 1)
      {
        $realUrl = rtrim($realUrl, $divider);
      }
    }

    if ('/' != $realUrl && '/' != substr($realUrl, -1))
    {
      $realUrl .= $suffix;
    }

    return $realUrl;
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
    // an URL should start with a '/', mod_rewrite doesn't respect that, but no-mod_rewrite version does.
    if ($url && ('/' != $url[0]))
    {
      $url = '/'.$url;
    }

    // we remove the query string
    if ($pos = strpos($url, '?'))
    {
      $url = substr($url, 0, $pos);
    }

    // we remove multiple /
    $url = preg_replace('#/+#', '/', $url);
    $out = array();
    $break = false;
    foreach ($this->routes as $routeName => $route)
    {
      $out = array();
      $r = null;

      list($route, $regexp, $names, $namesHash, $defaults, $requirements, $suffix) = $route;

      $break = false;

      if (preg_match($regexp, $url, $r))
      {
        $break = true;

        // remove the first element, which is the url
        array_shift($r);

        // hack, pre-fill the default route names
        foreach ($names as $name)
        {
          $out[$name] = null;
        }

        // defaults
        foreach ($defaults as $name => $value)
        {
          if (preg_match('#[a-z_\-]#i', $name))
          {
            $out[$name] = urldecode($value);
          }
          else
          {
            $out[$value] = true;
          }
        }

        $pos = 0;
        foreach ($r as $found)
        {
          // if $found is a named url element (i.e. ':action')
          if (isset($names[$pos]))
          {
            $out[$names[$pos]] = urldecode($found);
          }
          // unnamed elements go in as 'pass'
          else
          {
            $pass = explode('/', $found);
            $found = '';
            for ($i = 0, $max = count($pass); $i < $max; $i += 2)
            {
              if (!isset($pass[$i + 1]))
              {
                continue;
              }

              $found .= $pass[$i].'='.$pass[$i + 1].'&';
            }
            parse_str($found, $pass);

            if (get_magic_quotes_gpc())
            {
              $pass = sfToolkit::stripslashesDeep((array) $pass);
            }

            foreach ($pass as $key => $value)
            {
              // we add this parameters if not in conflict with named url element (i.e. ':action')
              if (!isset($namesHash[$key]))
              {
                $out[$key] = $value;
              }
            }
          }
          $pos++;
        }

        // we must have found all :var stuffs in url? except if default values exists
        foreach ($names as $name)
        {
          if (is_null($out[$name]))
          {
            $break = false;
          }
        }

        if ($break)
        {
          // we store route name
          $this->currentRouteName = $routeName;
          $this->currentInternalUri = array();

          if ($this->options['logging'])
          {
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Match route [%s] "%s"', $routeName, $route))));
          }

          break;
        }
      }
    }

    // no route found
    if (!$break)
    {
      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array('No matching route found')));
      }

      $this->currentRouteParameters = null;

      throw new sfError404Exception(sprintf('No matching route found for "%s"', $url));
    }

    $this->currentRouteParameters = $out;

    return $this->currentRouteParameters;
  }
}
