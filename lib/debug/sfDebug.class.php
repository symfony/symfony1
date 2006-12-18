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
  public static function phpInfoAsArray()
  {
    $values = array(
      'php' => phpversion(),
      'os' => php_uname(),
      'extensions' => get_loaded_extensions(),
    );

    return $values;
  }

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

  public static function settingsAsArray()
  {
    $config = sfConfig::getAll();

    ksort($config);

    return $config;
  }

  public static function requestAsArray($request)
  {
    if ($request)
    {
      $values = array(
        'parameterHolder' => self::flattenParameterHolder($request->getParameterHolder()),
        'attributeHolder' => self::flattenParameterHolder($request->getAttributeHolder()),
      );
    }
    else
    {
      $values = array('parameterHolder' => array(), 'attributeHolder' => array());
    }

    return $values;
  }

  public static function responseAsArray($response)
  {
    if ($response)
    {
      $values = array(
        'cookies'         => array(),
        'httpHeaders'     => array(),
        'parameterHolder' => self::flattenParameterHolder($response->getParameterHolder()),
      );
      foreach ($response->getHttpHeaders() as $key => $value)
      {
        $values['httpHeaders'][$key] = $value;
      }

      $cookies = array();
      foreach ($response->getCookies() as $key => $value)
      {
        $values['cookies'][$key] = $value;
      }
    }
    else
    {
      $values = array('cookies' => array(), 'httpHeaders' => array(), 'parameterHolder' => array());
    }

    return $values;
  }

  public static function flattenParameterHolder($parameterHolder)
  {
    $values = array();
    foreach ($parameterHolder->getNamespaces() as $ns)
    {
      $values[$ns] = array();
      foreach ($parameterHolder->getAll($ns) as $key => $value)
      {
        $values[$ns][$key] = $value;
      }
      ksort($values[$ns]);
    }

    ksort($values);

    return $values;
  }
}
