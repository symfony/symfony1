<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
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
  /**
   * Generate a formatted symfony URL.
   *
   * @param string An existing URL for basing the parameters.
   * @param array  An associative array of URL parameters.
   *
   * @return string A URL to a symfony resource.
   */
   public function genURL($url = null, $parameters = array())
   {
     // absolute URL or symfony URL?
     if (!is_array($parameters) && preg_match('/^(http|ftp)/', $parameters))
     {
       return $parameters;
     }

     if (!is_array($parameters) && $parameters == '#')
     {
       return $parameters;
     }

     $url = '';
     if (!SF_NO_SCRIPT_NAME)
     {
       $url = $_SERVER['SCRIPT_NAME'];
     }
     else if (SF_RELATIVE_URL_ROOT && SF_NO_SCRIPT_NAME)
     {
       $url = SF_RELATIVE_URL_ROOT;
     }

     $route_name = '';

     if (!is_array($parameters))
     {
       list($route_name, $parameters) = $this->convertUrlStringToParameters($parameters);
     }

     if (SF_URL_FORMAT == 'PATH')
     {
       // use PATH format
       $divider = '/';
       $equals  = '/';
       $url    .= '/';
     }
     else
     {
       // use GET format
       $divider = '&';
       $equals  = '=';
       $url    .= '?';
     }

     // default module
     if (!isset($parameters['module']))
     {
       $parameters['module'] = SF_DEFAULT_MODULE;
     }

     // default action
     if (!isset($parameters['action']))
     {
       $parameters['action'] = SF_DEFAULT_ACTION;
     }

     $r = sfRouting::getInstance();
     if ($r->hasRoutes() && $generated_url = $r->generate($route_name, $parameters, $divider, $equals))
     {
       // strip off first divider character
       $url .= ltrim($generated_url, $divider);
     }
     else
     {
       // loop through the parameters
       foreach ($parameters as $key => &$value)
       {
         $url .= urlencode($key).$equals.urlencode($value).$divider;
       }

       // strip off last divider character
       $url = rtrim($url, $divider);
     }

     return $url;
   }

   public function convertUrlStringToParameters($url)
   {
     $params       = array();
     $query_string = '';
     $route_name   = '';

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
     if ($url{0} == '/')
     {
       $url = substr($url, 1);
     }

     // route_name?
     if ($url{0} == '@')
     {
       $route_name = substr($url, 1);
     }
     else
     {
       $tmp = explode('/', $url);

       $params['module'] = $tmp[0];
       $params['action'] = isset($tmp[1]) ? $tmp[1] : SF_DEFAULT_ACTION;
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
   * Initialize this controller.
   *
   * @return void
   */
  public function initialize ($context)
  {
    // initialize parent
    parent::initialize($context);
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
    // redirect
    header('Location: '.$url);

    $echo = '<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>';
    $echo = sprintf($echo, $delay, $url);
    echo $echo;
  }

  /**
   * Set the content type for this request.
   *
   * @param string A content type.
   *
   * @return void
   */
  public function setContentType ($type)
  {
    $this->contentType = $type;
  }
}

?>