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
 * sfConfigHandler allows a developer to create a custom formatted configuration
 * file pertaining to any information they like and still have it auto-generate
 * PHP code.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfConfigHandler
{
  protected $parameter_holder = null;

  /**
   * Add a set of replacement values.
   *
   * @param string The old value.
   * @param string The new value which will replace the old value.
   *
   * @return void
   */
  public function addReplacement ($oldValue, $newValue)
  {
    $this->oldValues[] = $oldValue;
    $this->newValues[] = $newValue;
  }

  /**
   * Execute this configuration handler.
   *
   * @param string An absolute filesystem path to a configuration file.
   *
   * @return string Data to be written to a cache file.
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file
   *                                       does not exist or is not readable.
   * @throws <b>sfParseException</b> If a requested configuration file is
   *                               improperly formatted.
   */
  abstract function & execute ($config, $param = array());

  /**
   * Initialize this ConfigHandler.
   *
   * @param array An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this ConfigHandler.
   */
  public function initialize ($parameters = null)
  {
    $this->getParameterHolder()->add($parameters);
  }

  public function __construct()
  {
    $this->parameter_holder = new sfParameterHolder();
  }

  /**
   * Literalize a string value.
   *
   * @param string The value to literalize.
   *
   * @return string A literalized value.
   */
  public static function literalize ($value)
  {
    static
      $keys = array("\\", "%'", "'"),
      $reps = array("\\\\", "\"", "\\'");

    if ($value == null)
    {
      // null value
      return 'null';
    }

    // lowercase our value for comparison
    $value  = trim($value);
    $lvalue = strtolower($value);

    if ($lvalue == 'on' || $lvalue == 'yes' || $lvalue == 'true')
    {
      // replace values 'on' and 'yes' with a boolean true value
      return 'true';
    }
    else if ($lvalue == 'off' || $lvalue == 'no' || $lvalue == 'false')
    {
      // replace values 'off' and 'no' with a boolean false value
      return 'false';
    }
    else if (!is_numeric($value))
    {
      $value = str_replace($keys, $reps, $value);

      return "'".$value."'";
    }

    // numeric value
    return $value;
  }

  /**
   * Replace constant identifiers in a string.
   *
   * @param string The value on which to run the replacement procedure.
   *
   * @return string The new value.
   */
  public static function & replaceConstants ($value)
  {
    $value = preg_replace('/%(.+?)%/e', 'constant("\\1")', $value);

    return $value;
  }

  /**
   * Replace a relative filesystem path with an absolute one.
   *
   * @param string A relative filesystem path.
   *
   * @return string The new path.
   */
  public static function & replacePath ($path)
  {
    if (!sfToolkit::isPathAbsolute($path))
    {
      // not an absolute path so we'll prepend to it
      $path = SF_APP_DIR.'/'.$path;
    }

    return $path;
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }
}

?>