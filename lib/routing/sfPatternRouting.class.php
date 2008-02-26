<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPatternRouting class controls the generation and parsing of URLs.
 *
 * It maps an array of parameters to URLs definition. Each map is called a route.
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
   * Initializes this Routing.
   *
   * Available options:
   *
   *  * suffix:             The default suffix
   *  * variable_prefixes:  An array of characters that starts a variable name (: by default)
   *  * segment_separators: An array of allowed characters for segment separators (/ and . by default)
   *  * variable_regex:     A regex that match a valid variable name ([\w\d_]+ by default)
   *
   * @see sfRouting
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    if (!isset($options['variable_prefixes']))
    {
      $options['variable_prefixes'] = array(':');
    }

    if (!isset($options['segment_separators']))
    {
      $options['segment_separators'] = array('/', '.');
    }

    if (!isset($options['variable_regex']))
    {
      $options['variable_regex'] = '[\w\d_]+';
    }

    $options['variable_prefix_regex']    = '(?:'.implode('|', array_map(create_function('$a', 'return preg_quote($a, \'#\');'), $options['variable_prefixes'])).')';
    $options['segment_separators_regex'] = '(?:'.implode('|', array_map(create_function('$a', 'return preg_quote($a, \'#\');'), $options['segment_separators'])).')';
    $options['variable_content_regex']   = '[^'.implode('', array_map(create_function('$a', 'return str_replace(\'-\', \'\-\', preg_quote($a, \'#\'));'), $options['segment_separators'])).']+';

    parent::initialize($dispatcher, $options);

    $this->setDefaultSuffix(isset($options['suffix']) ? $options['suffix'] : '');
  }

  /**
   * @see sfRouting
   */
  public function loadConfiguration()
  {
    if ($config = sfContext::getInstance()->getConfigCache()->checkConfig('config/routing.yml', true))
    {
      include($config);
    }

    parent::loadConfiguration();
  }

  /**
   * @see sfRouting
   */
  public function getCurrentInternalUri($withRouteName = false)
  {
    if (is_null($this->currentRouteName))
    {
      return null;
    }

    $typeId = $withRouteName ? 0 : 1;

    if (!isset($this->currentInternalUri[$typeId]))
    {
      $parameters = $this->currentRouteParameters;

      list($url, $regex, $variables, $defaults, $requirements) = $this->routes[$this->currentRouteName];

      $internalUri = $withRouteName ? '@'.$this->currentRouteName : $parameters['module'].'/'.$parameters['action'];

      $params = array();

      // add parameters
      foreach (array_keys($variables) as $variable)
      {
        if ($variable == 'module' || $variable == 'action')
        {
          continue;
        }

        $params[] = $variable.'='.(isset($parameters[$variable]) ? $parameters[$variable] : (isset($defaults[$variable]) ? $defaults[$variable] : ''));
      }

      // add * parameters if needed
      if (false !== strpos($regex, '_star'))
      {
        foreach ($parameters as $key => $value)
        {
          if ($key == 'module' || $key == 'action' || isset($variables[$key]))
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
   * @see sfRouting
   */
  public function getRoutes()
  {
    return $this->routes;
  }

  /**
   * @see sfRouting
   */
  public function setRoutes($routes)
  {
    return $this->routes = $routes;
  }

  /**
   * @see sfRouting
   */
  public function hasRoutes()
  {
    return count($this->routes) ? true : false;
  }

  /**
   * @see sfRouting
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
   * $r->connect('default', '/:module/:action/*');
   * </code>
   *
   * @param  string The route name
   * @param  string The route string
   * @param  array  The default parameter values
   * @param  array  The regexps parameters must match
   *
   * @return array  current routes
   */
  public function connect($name, $route, $defaults = array(), $requirements = array())
  {
    // route already exists?
    if (isset($this->routes[$name]))
    {
      throw new sfConfigurationException(sprintf('This named route already exists ("%s").', $name));
    }

    $suffix = $this->defaultSuffix;
    $route  = trim($route);

    // fix defaults
    foreach ($defaults as $key => $value)
    {
      if (ctype_digit($key))
      {
        $defaults[$value] = true;
      }
      else
      {
        $defaults[$key] = urldecode($value);
      }
    }
    $defaults = $this->fixDefaults($defaults);

    // fix requirements regexs
    foreach ($requirements as $key => $regex)
    {
      if ('^' == $regex[0])
      {
        $regex = substr($regex, 1);
      }
      if ('$' == substr($regex, -1))
      {
        $regex = substr($regex, 0, -1);
      }

      $requirements[$key] = $regex;
    }

    // a route can start by a slash. remove it for parsing.
    if (!empty($route) && '/' == $route[0])
    {
      $route = substr($route, 1); 
    }

    if ($route == '')
    {
      $this->routes[$name] = array('/', '/^\/*$/', array(), $defaults, $requirements);
    }
    else
    {
      // ignore the default suffix if one is already provided in the route
      if ('/' == $route[strlen($route) - 1])
      {
        // route ends by / (directory)
        $suffix = '';
      }
      else if ('.' == $route[strlen($route) - 1])
      {
        // route ends by . (no suffix)
        $suffix = '';
        $route = substr($route, 0, strlen($route) -1); 
      }
      else if (preg_match('#\.(?:'.$this->options['variable_prefix_regex'].$this->options['variable_regex'].'|'.$this->options['variable_content_regex'].')$#i', $route))
      {
        // specific suffix for this route
        // a . with a variable after or some cars without any separators
        $suffix = '';
      }

      // parse the route
      $segments = array();
      $firstOptional = 0;
      $buffer = $route;
      $afterASeparator = true;
      $currentSeparator = '';
      $variables = array();

      // a route is an array of (separator + variable) or (separator + text) segments
      while (strlen($buffer))
      {
        if ($afterASeparator && preg_match('#^'.$this->options['variable_prefix_regex'].'('.$this->options['variable_regex'].')#', $buffer, $match))
        {
          // a variable (like :foo)
          $variable = $match[1];

          if (!isset($requirements[$variable]))
          {
            $requirements[$variable] = $this->options['variable_content_regex'];
          }

          $segments[] = $currentSeparator.'(?P<'.$variable.'>'.$requirements[$variable].')';
          $currentSeparator = '';

          if (!isset($defaults[$variable]))
          {
            $defaults[$variable] = null;
          }

          $buffer = substr($buffer, strlen($match[0]));
          $variables[$variable] = $match[0];
          $afterASeparator = false;
        }
        else if ($afterASeparator)
        {
          // a static text
          if (!preg_match('#^(.+?)(?:'.$this->options['segment_separators_regex'].'|$)#', $buffer, $match))
          {
            throw new InvalidArgumentException(sprintf('Unable to parse "%s" route near "%s".', $route, $buffer));
          }

          if ('*' == $match[1])
          {
            $segments[] = '(?:'.$currentSeparator.'(?P<_star>.*))?';
          }
          else
          {
            $segments[] = $currentSeparator.preg_quote($match[1], '#');
            $firstOptional = count($segments);
          }
          $currentSeparator = '';

          $buffer = substr($buffer, strlen($match[1]));
          $afterASeparator = false;
        }
        else if (preg_match('#^'.$this->options['segment_separators_regex'].'#', $buffer, $match))
        {
          // a separator (like / or .)
          $currentSeparator = preg_quote($match[0], '#');

          $buffer = substr($buffer, strlen($match[0]));
          $afterASeparator = true;
        }
        else
        {
          // parsing problem
          throw new InvalidArgumentException(sprintf('Unable to parse "%s" route near "%s".', $route, $buffer));
        }
      }

      // all segments after the last static segment are optional
      // be careful, the n-1 is optional only if n is empty
      for ($i = $firstOptional, $max = count($segments); $i < $max; $i++)
      {
        $segments[$i] = str_repeat(' ', $i - $firstOptional).'(?:'.$segments[$i];
        $segments[] = str_repeat(' ', $max - $i - 1).')?';
      }

      $regex = "#^/\n".implode("\n", $segments)."\n".$currentSeparator.preg_quote($suffix, '#')."$#x";
      $this->routes[$name] = array('/'.$route.$suffix, $regex, $variables, $defaults, $requirements);
    }

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Connect "%s"%s', $route, $suffix ? ' ("'.$suffix.'" suffix)' : ''))));
    }

    return $this->routes;
  }

  /**
   * @see sfRouting
   */
  public function generate($name, $params, $querydiv = '/', $divider = '/', $equals = '/')
  {
    $params = $this->fixDefaults($params);

    // named route?
    if ($name)
    {
      if (!isset($this->routes[$name]))
      {
        throw new sfConfigurationException(sprintf('The route "%s" does not exist.', $name));
      }

      list($url, $regex, $variables, $defaults, $requirements) = $this->routes[$name];
      $defaults = $this->mergeArrays($defaults, $this->defaultParameters);
      $tparams = $this->mergeArrays($defaults, $params);

      // all params must be given
      if ($diff = array_diff_key($variables, array_filter($tparams, create_function('$v', 'return !is_null($v);'))))
      {
        throw new InvalidArgumentException(sprintf('The "%s" route has some missing mandatory parameters (%s).', $name, implode(', ', $diff)));
      }
    }
    else
    {
      // find a matching route
      $found = false;
      foreach ($this->routes as $name => $route)
      {
        list($url, $regex, $variables, $defaults, $requirements) = $route;
        $defaults = $this->mergeArrays($defaults, $this->defaultParameters);
        $tparams = $this->mergeArrays($defaults, $params);

        // all $variables must be defined in the $tparams array
        if (array_diff_key($variables, array_filter($tparams)))
        {
          continue;
        }

        // check requirements
        foreach ($requirements as $reqParam => $reqRegexp)
        {
          if (!is_null($tparams[$reqParam]) && !preg_match('#'.$reqRegexp.'#', $tparams[$reqParam]))
          {
            continue 2;
          }
        }

        // all $params must be in $variables or $defaults if there is no * in route
        if (false === strpos($regex, '_star') && array_diff_key(array_filter($params), $variables, $defaults))
        {
          continue;
        }

        // check that $params does not override a default value that is not a variable
        foreach (array_filter($defaults) as $key => $value)
        {
          if (!isset($variables[$key]) && $tparams[$key] != $value)
          {
            continue 2;
          }
        }

        // found
        $found = true;
        break;
      }

      if (!$found)
      {
        throw new sfConfigurationException(sprintf('Unable to find a matching routing rule to generate url for params "%s".', var_export($params, true)));
      }
    }

    // replace variables
    $realUrl = $url;
    foreach ($variables as $variable => $value)
    {
      $realUrl = str_replace($value, urlencode($tparams[$variable]), $realUrl);
    }

    // add extra params if the route contains *
    if (false !== strpos($regex, '_star'))
    {
      $tmp = array();
      foreach (array_diff_key($tparams, $variables, $defaults) as $key => $value)
      {
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
      if ($tmp)
      {
        $tmp = $querydiv.$tmp;
      }

      $realUrl = preg_replace('#'.$this->options['segment_separators_regex'].'\*('.$this->options['segment_separators_regex'].'|$)#', "$tmp$1", $realUrl);
    }

    return $realUrl;
  }

  /**
   * @see sfRouting
   */
  public function parse($url)
  {
    // an URL should start with a '/', mod_rewrite doesn't respect that, but no-mod_rewrite version does.
    if ('/' != $url[0])
    {
      $url = '/'.$url;
    }

    // we remove the query string
    if (false !== $pos = strpos($url, '?'))
    {
      $url = substr($url, 0, $pos);
    }

    // remove multiple /
    $url = preg_replace('#/+#', '/', $url);

    $found = false;
    foreach ($this->routes as $routeName => $route)
    {
      list($route, $regex, $variables, $defaults, $requirements) = $route;
      if (!preg_match($regex, $url, $r))
      {
        continue;
      }

      $defaults = array_merge($defaults, $this->defaultParameters);
      $found    = true;
      $out      = array();

      // *
      if (isset($r['_star']))
      {
        $out = $this->parseStarParameter($r['_star']);
        unset($r['_star']);
      }

      // defaults
      $out = $this->mergeArrays($out, $defaults);

      // variables
      foreach ($r as $key => $value)
      {
        if (!is_int($key))
        {
          $out[$key] = $value;
        }
      }

      // store the route name
      $this->currentRouteName = $routeName;
      $this->currentInternalUri = array();

      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Match route [%s] for "%s"', $routeName, $route))));
      }

      break;
    }

    // no route found
    if (!$found)
    {
      throw new sfError404Exception(sprintf('No matching route found for "%s"', $url));
    }

    return $this->currentRouteParameters = $this->fixDefaults($out);
  }

  protected function parseStarParameter($star)
  {
    $parameters = array();
    $tmp = explode('/', $star);
    for ($i = 0, $max = count($tmp); $i < $max; $i += 2)
    {
      $parameters[$tmp[$i]] = isset($tmp[$i + 1]) ? urldecode($tmp[$i + 1]) : true;
    }

    return $parameters;
  }
}
