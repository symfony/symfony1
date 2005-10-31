<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id: sfRouting.class.php 522 2005-10-17 08:23:26Z fabien $
 */

/**
 * sfRouting class controls the creation of URLs and parses URLs. It maps an array of parameters to URLs definition.
 * Each map is called a route.
 * It implements the Singleton pattern.
 *
 * Routing is disabled when SF_ROUTING is set to false.
 *
 * This class is based on the Routes class of Cake framework.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id: sfRouting.class.php 522 2005-10-17 08:23:26Z fabien $
 */
class sfRouting
{
  private static
    $instance = null;

  private
    $routes   = array();

  /**
   * Returns the sfRouting instance.
   *
   * @return  object the sfRouting instance
   */
  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfRouting();
    }

    return self::$instance;
  }

  public function getRoutes()
  {
    return $this->routes;
  }

  public function setRoutes($routes)
  {
    return $this->routes = $routes;
  }

  /**
   * Has this instance some routes.
   *
   * @return  object the sfLogger instance
   */
  public function hasRoutes()
  {
    return count($this->routes) ? true : false;
  }

  /**
   * Clears all current routes.
   *
   */
  public function clearRoutes()
  {
    if (SF_LOGGING_ACTIVE) sfLogger::getInstance()->info('{sfRouting} clear all current routes');

    $this->routes = array();
  }

 /**
  * Adds a new route.
  *
  * A route string is a string with 2 special constructions:
  * - :string: :string denotes a named paramater (available later as $request->getParameter('string'))
  * - *: * match an indefinite number of parameters in a route
  *
  * Here is the 2 most common rules in a SymFony project:
  *
  * <code>
  * $r->connect('/', array('module' => SF_DEFAULT_MODULE, 'action' => SF_DEFAULT_ACTION));
  * $r->connect('/:module/:action/*');
  * </code>
  *
  * @param  string a route string
  * @param  array  default parameter values
  * @param  array  regexps parameters must match 
  * @return array  current routes
  */
  public function connect($name, $route, $default = array(), $requirements = array())
  {
    if (!SF_ROUTING)
    {
      return array();
    }

    // route already exists
    if (isset($this->routes[$name]))
    {
      $error = 'This named route already exists ("%s").';
      $error = sprintf($error, $name);

      throw new sfConfigurationException($error);
    }

    if (SF_LOGGING_ACTIVE) sfLogger::getInstance()->info('{sfRouting} connect "'.$route.'"');

    $parsed = array();
    $names = array();

    // used for performance reasons
    $names_hash = array();

    $r = null;
    if (($route == '') || ($route == '/'))
    {
      $regexp = '/^[\/]*$/';
      $this->routes[$name] = array($route, $regexp, array(), array(), $default, $requirements);
    }
    else
    {
      $elements = array();
      foreach (explode('/', $route) as $element)
      {
        if (trim($element)) $elements[] = $element;
      }

      if (!isset($elements[0]))
      {
        return false;
      }

      foreach ($elements as $element)
      {
        if (preg_match('/^:(.+)$/', $element, $r))
        {
          $parsed[] = '(?:\/([^\/]+))?';
          $names[] = $r[1];
          $names_hash[$r[1]] = 1;
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
      $regexp = '#^'.join('', $parsed).'[\/]*$#';

      $this->routes[$name] = array($route, $regexp, $names, $names_hash, $default, $requirements);
    }

    return $this->routes;
  }

 /**
  * Generates a valid URLs for parameters.
  *
  * @param  array  parameter values
  * @param  string divider between key/value pairs
  * @param  string equal sign to use between key and value
  * @return string url
  */
  public function generate($name, $params, $divider, $equals)
  {
    if (!SF_ROUTING) return array();

    // named route?
    if ($name)
    {
      if (!isset($this->routes[$name]))
      {
        $error = 'The route "%s" does not exist.';
        $error = sprintf($error, $name);

        throw new sfConfigurationException($error);
      }

      list($url, $regexp, $names, $names_hash, $defaults) = $this->routes[$name];
    }
    else
    {
      // find a matching route
      $found = false;
      foreach ($this->routes as $name => $route)
      {
        list($url, $regexp, $names, $names_hash, $defaults) = $route;

        // we must match all names (all $names keys must be in $params array)
        foreach ($names as $key)
        {
          if (!isset($params[$key])) continue 2;
        }

        // we must match all defaults with value except if present in names
        foreach ($defaults as $key => $value)
        {
          if (isset($names_hash[$key])) continue;

          if (!isset($params[$key]) || $params[$key] != $value) continue 2;
        }

        // we must have consume all $params keys if there is no * in route
        if (!strpos($url, '*'))
        {
          if (count(array_diff(array_keys($params), $names, array_keys($defaults))))
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
        $error = 'Unable to find a matching routing rule to generate url for params "%s".';
        $error = sprintf($error, var_export($params));

        throw new sfConfigurationException($error);
      }
    }

    $real_url = preg_replace('/\:([^\/]+)/e', 'urlencode($params["\\1"])', $url);

    // we add all other params if *
    if (strpos($real_url, '*'))
    {
      $tmp = '';
      foreach ($params as $key => $value)
      {
        if (isset($names_hash[$key]) || isset($defaults[$key])) continue;

        if (is_array($value))
        {
          foreach ($value as $v)
            $tmp .= $key.$equals.urlencode($v).$divider;
        }
        else
        {
          $tmp .= urlencode($key).$equals.urlencode($value).$divider;
        }
      }

      $real_url = preg_replace('/\*(\/|$)/', $tmp, $real_url);
    }

    // strip off last divider character
    if (isset($real_url{1}))
    {
      $real_url = rtrim($real_url, $divider);
    }

    if ($real_url != '/')
    {
      $real_url .= SF_SUFFIX;
    }

    return $real_url;
  }

 /**
  * Parses a URL to find a matching route.
  *
  * Returns null if no route match the URL.
  *
  * @param  string URL to be parsed
  * @return array  parameters
  */
  public function parse($url) 
  {
    if (!SF_ROUTING) return array();

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

    // we remove the suffix
    $url = preg_replace('/'.SF_SUFFIX.'$/', '', $url);

    // we remove multiple /
    $url = preg_replace('#/+#', '/', $url);

    foreach ($this->routes as $name => $route) 
    {
      $out = array();
      $r = null;

      list($route, $regexp, $names, $names_hash, $defaults, $requirements) = $route;

      $break = false;

      if (preg_match($regexp, $url, $r)) 
      {
        $break = true;

        // remove the first element, which is the url
        array_shift($r);

        // hack, pre-fill the default route names
        foreach ($names as $name) $out[$name] = null;

        // defaults
        foreach ($defaults as $name => $value) 
        {
          if (preg_match('#[a-z_\-]#i', $name))
          {
            $out[$name] = $value;
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
            // check requirements
            if (isset($requirements[$names[$pos]]) && !preg_match('/'.$requirements[$names[$pos]].'/', $found))
            {
              $break = false;
              break;
            }
            $out[$names[$pos]] = $found;
          }
          // unnamed elements go in as 'pass'
          else 
          {
            $pass = explode('/', $found);
            for ($i = 0, $max = count($pass); $i < $max; $i += 2)
            {
              if (!isset($pass[$i + 1])) continue;

              $key = $pass[$i];
              $value = $pass[$i + 1];

              // we add this parameters if not in conflict with named url element (i.e. ':action')
              if (!isset($names_hash[$key]))
              {
                // array parameters?
                if (substr($key, -2) == '[]')
                {
                  if (!isset($out[$key])) $out[$key] = array();
                  $out[$key][] = $value;
                }
                else
                {
                  $out[$key] = $value;
                }
              }
            }
          }
          $pos++;
        }

        // we must have found all :var stuffs in url? except if default values exists
        foreach ($names as $name)
        {
          if ($out[$name] == null) $break = false;
        }

        if ($break)
        {
          if (SF_LOGGING_ACTIVE) sfLogger::getInstance()->info('{sfRouting} match route "'.$route.'"');
          break;
        }
      }
    }

    // no route found
    if (!$break)
    {
      return null;
    }

    return $out;
  }
}

?>