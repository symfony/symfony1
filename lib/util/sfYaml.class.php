<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYaml class.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfYaml
{
  /**
   * Load YAML into a PHP array statically
   *
   * The load method, when supplied with a YAML stream (string or file),
   * will do its best to convert YAML in a file into a PHP array.
   *
   *  Usage:
   *  <code>
   *   $array = sfYAML::Load('config.yml');
   *   print_r($array);
   *  </code>
   *
   * @return array
   * @param string $input Path of YAML file or string containing YAML
   */
  public static function load ($input)
  {
    // syck is prefered over spyc
    if (function_exists('syck_load')) {
      if (!empty($input) && is_readable($input))
      {
        $input = file_get_contents($input);
      }

      return syck_load($input);
    }
    else
    {
      $spyc = new Spyc();

      return $spyc->load($input);
    }
  }

  /**
   * Dump YAML from PHP array statically
   *
   * The dump method, when supplied with an array, will do its best
   * to convert the array into friendly YAML.
   *
   * @return string
   * @param array $array PHP array
   */
  public static function dump ($array)
  {
    $spyc = new Spyc();

    return $spyc->dump($array);
  }
}

?>