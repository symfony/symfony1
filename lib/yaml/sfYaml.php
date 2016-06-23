<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYaml offers convenience methods to load and dump YAML.
 *
 * @package    symfony
 * @subpackage yaml
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfYaml.class.php 8988 2008-05-15 20:24:26Z fabien $
 */
class sfYaml
{
  static protected
    $spec = '1.2';

  /**
   * Sets the YAML specification version to use.
   *
   * @param string $version The YAML specification version
   */
  static public function setSpecVersion($version)
  {
    if (!in_array($version, array('1.1', '1.2')))
    {
      throw new InvalidArgumentException(sprintf('Version %s of the YAML specifications is not supported', $version));
    }

    self::$spec = $version;
  }

  /**
   * Gets the YAML specification version to use.
   *
   * @return string The YAML specification version
   */
  static public function getSpecVersion()
  {
    return self::$spec;
  }

  /**
   * Loads YAML into a PHP array.
   *
   * The load method, when supplied with a YAML stream (string or file),
   * will do its best to convert YAML in a file into a PHP array.
   *
   *  Usage:
   *  <code>
   *   $array = sfYaml::load('config.yml');
   *   print_r($array);
   *  </code>
   *
   * @param string $input Path of YAML file or string containing YAML
   *
   * @return array The YAML converted to a PHP array
   *
   * @throws InvalidArgumentException If the YAML is not valid
   */
  public static function load($input)
  {
    $file = '';

    // if input is a file, process it
    if (strpos($input, "\n") === false && is_file($input))
    {
      $file = $input;

      ob_start();
      $retval = include($input);
      $content = ob_get_clean();

      // if an array is returned by the config file assume it's in plain php form else in YAML
      $input = is_array($retval) ? $retval : $content;
    }

    // if an array is returned by the config file assume it's in plain php form else in YAML
    if (is_array($input))
    {
      return $input;
    }

    require_once dirname(__FILE__).'/sfYamlParser.php';

    $yaml = new sfYamlParser();

    try
    {
      $ret = $yaml->parse($input);

      self::parseImports($ret, $file);
    }
    catch (Exception $e)
    {
      throw new InvalidArgumentException(sprintf('Unable to parse %s: %s', $file ? sprintf('file "%s"', $file) : 'string', $e->getMessage()));
    }

    return $ret;
  }

  /**
   * Parses all imports.
   *
   * @param array  $content
   * @param string $file
   */
  private static function parseImports(&$content, $file)
  {
    if (!isset($content['imports']) || !is_array($content))
    {
      return;
    }
    if (!is_array($content['imports']))
    {
      throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
    }
    $directory = dirname($file);
    $previous_data = array();
    foreach ($content['imports'] as $import)
    {
      if (!is_array($import))
      {
        throw new InvalidArgumentException(sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file));
      }
      $previous_data = self::array_merge_recursive2(
        $previous_data,
        self::load($directory . DIRECTORY_SEPARATOR . $import['resource'])
      );
    }
    $content = self::array_merge_recursive2(
      $previous_data,
      $content
    );
    unset($content['imports']);
  }

  /**
   * array_merge_recursive2()
   *
   * Similar to array_merge_recursive but keyed-valued are always overwritten.
   * Priority goes to the 2nd array.
   *
   * @source http://php.net/manual/en/function.array-merge-recursive.php
   *
   * @static yes
   * @public yes
   * @param $paArray1 array
   * @param $paArray2 array
   * @return array
   */
  private static function array_merge_recursive2($paArray1, $paArray2)
  {
    if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
    foreach ($paArray2 AS $sKey2 => $sValue2)
    {
      $paArray1[$sKey2] = self::array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
    }
    return $paArray1;
  }

  /**
   * Dumps a PHP array to a YAML string.
   *
   * The dump method, when supplied with an array, will do its best
   * to convert the array into friendly YAML.
   *
   * @param array   $array PHP array
   * @param integer $inline The level where you switch to inline YAML
   *
   * @return string A YAML string representing the original PHP array
   */
  public static function dump($array, $inline = 2)
  {
    require_once dirname(__FILE__).'/sfYamlDumper.php';

    $yaml = new sfYamlDumper();

    return $yaml->dump($array, $inline);
  }
}

/**
 * Wraps echo to automatically provide a newline.
 *
 * @param string $string The string to echo with new line
 */
function echoln($string)
{
  echo $string."\n";
}
