<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
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
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfWebController extends sfController
{
  /**
   * Generates an URL from an array of parameters.
   *
   * @param mixed   $parameters An associative array of URL parameters or an internal URI as a string.
   * @param boolean $absolute   Whether to generate an absolute URL
   *
   * @return string A URL to a symfony resource
   */
  public function genUrl($parameters = array(), $absolute = false)
  {
    // absolute URL or symfony URL?
    if (!is_array($parameters) && preg_match('#^[a-z][a-z0-9\+.\-]*\://#i', $parameters))
    {
      return $parameters;
    }

    if (!is_array($parameters) && $parameters == '#')
    {
      return $parameters;
    }

    if (!sfConfig::get('sf_no_script_name'))
    {
      $url = sfConfig::get('sf_relative_url_root', $this->context->getRequest()->getScriptName());
    }
    else
    {
      $url = $this->context->getRequest()->getRelativeUrlRoot();
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
      $querydiv = '/';
    }
    else
    {
      // use GET format
      $divider = ini_get('arg_separator.output');
      $equals  = '=';
      $querydiv = '?';
    }

    // routing to generate path
    $url .= $this->context->getRouting()->generate($route_name, $parameters, $querydiv, $divider, $equals);

    if ($absolute)
    {
      $request = $this->context->getRequest();
      $url = 'http'.($request->isSecure() ? 's' : '').'://'.$request->getHost().$url;
    }

    if ($fragment)
    {
      $url .= '#'.$fragment;
    }

    return $url;
  }

  /**
   * Converts an internal URI string to an array of parameters.
   *
   * @param string $url An internal URI
   *
   * @return array An array of parameters
   */
  public function convertUrlStringToParameters($url)
  {
    $givenUrl = $url;

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
    else if (false !== strpos($url, '/'))
    {
      list($params['module'], $params['action']) = explode('/', $url);
    }
    else
    {
      throw new InvalidArgumentException(sprintf('An internal URI must contain a module and an action (module/action) ("%s" given).', $givenUrl));
    }

    // split the query string
    if ($query_string)
    {
      $matched = preg_match_all('/
        ([^&=]+)            # key
        =                   # =
        (.*?)               # value
        (?:
          (?=&[^&=]+=) | $  # followed by another key= or the end of the string
        )
      /x', $query_string, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      foreach ($matches as $match)
      {
        $params[$match[1][0]] = $match[2][0];
      }

      // check that all string is matched
      if (!$matched)
      {
        throw new sfParseException(sprintf('Unable to parse query string "%s".', $query_string));
      }
    }

    return array($route_name, $params);
  }

  /**
   * Redirects the request to another URL.
   *
   * @param string $url         An existing URL
   * @param int    $delay       A delay in seconds before redirecting. This is only needed on
   *                            browsers that do not support HTTP headers
   * @param int    $statusCode  The status code
   */
  public function redirect($url, $delay = 0, $statusCode = 302)
  {
    $url = $this->genUrl($url, true);

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Redirect to "%s"', $url))));
    }

    // redirect
    $response = $this->context->getResponse();
    $response->clearHttpHeaders();
    $response->setStatusCode($statusCode);
    $response->setHttpHeader('Location', $url);
    $response->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', $delay, htmlspecialchars($url, ENT_QUOTES, sfConfig::get('sf_charset'))));
    $response->send();
  }
}
