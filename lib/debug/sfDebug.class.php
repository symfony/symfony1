<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebug provides some method to help debugging a symfony application.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfDebug
{
  /**
   * Returns symfony information as an array.
   *
   * @return array An array of symfony information
   */
  public static function symfonyInfoAsArray()
  {
    return array(
      'version' => SYMFONY_VERSION,
      'path'    => sfConfig::get('sf_symfony_lib_dir'),
    );
  }

  /**
   * Returns PHP information as an array.
   *
   * @return array An array of php information
   */
  public static function phpInfoAsArray()
  {
    $values = array(
      'php'        => phpversion(),
      'os'         => php_uname(),
      'extensions' => get_loaded_extensions(),
    );

    // assign extension version
    if ($values['extensions'])
    {
      foreach($values['extensions'] as $key => $extension)
      {
        $values['extensions'][$key] = phpversion($extension) ? sprintf('%s (%s)', $extension, phpversion($extension)) : $extension;
      }
    }

    return $values;
  }

  /**
   * Returns PHP globals variables as a sorted array.
   *
   * @return array PHP globals
   */
  public static function globalsAsArray()
  {
    $values = array();
    foreach (array('cookie', 'server', 'get', 'post', 'files', 'env', 'session') as $name)
    {
      if (!isset($GLOBALS['_'.strtoupper($name)]))
      {
        continue;
      }

      $values[$name] = array();
      foreach ($GLOBALS['_'.strtoupper($name)] as $key => $value)
      {
        $values[$name][$key] = $value;
      }
      ksort($values[$name]);
    }

    ksort($values);

    return $values;
  }

  /**
   * Returns sfConfig variables as a sorted array.
   *
   * @return array sfConfig variables
   */
  public static function settingsAsArray()
  {
    $config = sfConfig::getAll();

    ksort($config);

    return $config;
  }

  /**
   * Returns request parameter holders as an array.
   *
   * @param sfRequest $request A sfRequest instance
   *
   * @return array The request parameter holders
   */
  public static function requestAsArray(sfRequest $request = null)
  {
    if (!$request)
    {
      return array();
    }

    return array(
      'parameterHolder' => self::flattenParameterHolder($request->getParameterHolder()),
      'attributeHolder' => self::flattenParameterHolder($request->getAttributeHolder()),
    );
  }

  /**
   * Returns response parameters as an array.
   *
   * @param sfResponse $response A sfResponse instance
   *
   * @return array The response parameters
   */
  public static function responseAsArray(sfResponse $response = null)
  {
    if (!$response)
    {
      return array();
    }

    return array(
      'options'     => $response->getOptions(),
      'cookies'     => method_exists($response, 'getCookies')     ? $response->getCookies() : array(),
      'httpHeaders' => method_exists($response, 'getHttpHeaders') ? $response->getHttpHeaders() : array(),
      'javascripts' => method_exists($response, 'getJavascripts') ? $response->getJavascripts('ALL') : array(),
      'stylesheets' => method_exists($response, 'getStylesheets') ? $response->getStylesheets('ALL') : array(),
      'metas'       => method_exists($response, 'getMetas')       ? $response->getMetas() : array(),
      'httpMetas'   => method_exists($response, 'getHttpMetas')   ? $response->getHttpMetas() : array(),
    );
  }

  /**
   * Returns user parameters as an array.
   *
   * @param sfUser $user A sfUser instance
   *
   * @return array The user parameters
   */
  public static function userAsArray(sfUser $user = null)
  {
    if (!$user)
    {
      return array();
    }

    return array(
      'options'         => $user->getOptions(),
      'attributeHolder' => self::flattenParameterHolder($user->getAttributeHolder()),
      'culture'         => $user->getCulture(),
    );
  }

  /**
   * Returns a parameter holder as an array.
   *
   * @param sfParameterHolder $parameterHolder A sfParameterHolder instance
   *
   * @return array The parameter holder as an array
   */
  public static function flattenParameterHolder($parameterHolder)
  {
    $values = array();
    if ($parameterHolder instanceof sfNamespacedParameterHolder)
    {
      foreach ($parameterHolder->getNamespaces() as $ns)
      {
        $values[$ns] = array();
        foreach ($parameterHolder->getAll($ns) as $key => $value)
        {
          $values[$ns][$key] = $value;
        }
        ksort($values[$ns]);
      }
    }
    else
    {
      foreach ($parameterHolder->getAll() as $key => $value)
      {
        $values[$key] = $value;
      }
    }

    ksort($values);

    return $values;
  }
}
