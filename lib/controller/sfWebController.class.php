<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebController provides web specific methods to sfController such as, url redirection.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfWebController extends sfController
{
  private
    $redirectedUri = null;

  /**
   * Generate a formatted symfony URL.
   *
   * @param string An existing URL for basing the parameters.
   * @param array  An associative array of URL parameters.
   *
   * @return string A URL to a symfony resource.
   */
  public function genUrl($parameters = array(), $absolute = false)
  {
    // absolute URL or symfony URL?
    if (!is_array($parameters) && preg_match('#^[a-z]+\://#', $parameters))
    {
      return $parameters;
    }

    if (!is_array($parameters) && $parameters == '#')
    {
      return $parameters;
    }

    $url = '';
    if (!($sf_no_script_name = sfConfig::get('sf_no_script_name')))
    {
      $url = $_SERVER['SCRIPT_NAME'];
    }
    else if (($sf_relative_url_root = $this->getContext()->getRequest()->getRelativeUrlRoot()) && $sf_no_script_name)
    {
      $url = $sf_relative_url_root;
    }

    $route_name = '';
    $fragment = '';

    if (!is_array($parameters))
    {
      // strip fragment
      if (false !== ($pos = strpos($parameters, '#')))
      {
        $fragment = substr($parameters, $pos + 1);
        $parameters = substr($parameters, 0, $pos);
      }

      list($route_name, $parameters) = $this->convertUrlStringToParameters($parameters);
    }

    if (sfConfig::get('sf_url_format') == 'PATH')
    {
      // use PATH format
      $divider = '/';
      $equals  = '/';
      $url    .= '/';
    }
    else
    {
      // use GET format
      $divider = ini_get('arg_separator.output');
      $equals  = '=';
      $url    .= '?';
    }

    // default module
    if (!isset($parameters['module']))
    {
      $parameters['module'] = sfConfig::get('sf_default_module');
    }

    // default action
    if (!isset($parameters['action']))
    {
      $parameters['action'] = sfConfig::get('sf_default_action');
    }

    $r = sfRouting::getInstance();
    if ($r->hasRoutes() && $generated_url = $r->generate($route_name, $parameters, $divider, $equals))
    {
      // strip off first divider character
      $url .= ltrim($generated_url, $divider);
    }
    else
    {
      $query = http_build_query($parameters);

      if (sfConfig::get('sf_url_format') == 'PATH')
      {
        $query = strtr($query, ini_get('arg_separator.output').'=', '/');
      }

      $url .= $query;
    }

    if ($absolute)
    {
      $request = $this->getContext()->getRequest();
      $url = 'http'.($request->isSecure() ? 's' : '').'://'.$request->getHost().$url;
    }

    if ($fragment)
    {
      $url .= '#'.$fragment;
    }

    return $url;
  }

  public function convertUrlStringToParameters($url)
  {
    $params       = array();
    $query_string = '';
    $route_name   = '';

    // empty url?
    if (!$url)
    {
      $url = '/';
    }

    // we get the query string out of the url
    if ($pos = strpos($url, '?'))
    {
      $query_string = substr($url, $pos + 1);
      $url = substr($url, 0, $pos);
    }

    // 2 url forms
    // @route_name?key1=value1&key2=value2...
    // module/action?key1=value1&key2=value2...

    // first slash optional
    if ($url[0] == '/')
    {
      $url = substr($url, 1);
    }


    // route_name?
    if ($url[0] == '@')
    {
      $route_name = substr($url, 1);
    }
    else
    {
      $tmp = explode('/', $url);

      $params['module'] = $tmp[0];
      $params['action'] = isset($tmp[1]) ? $tmp[1] : sfConfig::get('sf_default_action');
    }

    $url_params = explode('&', $query_string);
    $ind_max = count($url_params) - 1;
    for ($i = 0; $i <= $ind_max; $i++)
    {
      if (!$url_params[$i]) continue;

      $pos = strpos($url_params[$i], '=');
      if ($pos === false)
      {
        $error = 'Unable to parse url ("%s").';
        $error = sprintf($error, $url);

        throw new sfParseException($error);
      }

      $params[substr($url_params[$i], 0, $pos)] = substr($url_params[$i], $pos + 1);
    }

    return array($route_name, $params);
  }

  /**
   * Redirect the request to another URL.
   *
   * @param string An existing URL.
   * @param int    A delay in seconds before redirecting. This only works on
   *               browsers that do not support the PHP header.
   *
   * @return void
   */
  public function redirect ($url, $delay = 0)
  {
    $this->redirectedUri = $url;

    $response = $this->getContext()->getResponse();

    // redirect
    $response->setHttpHeader('Location', $url);
    $response->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', $delay, htmlentities($url)));

    $response->sendHttpHeaders();
    $response->sendContent();
  }

  /**
   * get the URL of a redirection if any.
   *
   * @return redirectedURL null if empty
   */
  public function getRedirectedURI()
  {
    return $this->redirectedUri;
  }
}
