<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfInflector.class.php 432 2005-09-07 12:30:24Z fabien $
 */
class sfInflector
{
  public static function camelize($lower_case_and_underscored_word)
  {
    $tmp = $lower_case_and_underscored_word;
    $tmp = preg_replace('/\/(.?)/e', "'::'.strtoupper('\\1')", $tmp);
    $tmp = preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", $tmp);

    return $tmp;
  }

  public static function underscore($camel_cased_word)
  {
    $tmp = $camel_cased_word;
    $tmp = preg_replace('/::/', '/', $tmp);
    $tmp = preg_replace('/([A-Z]+)([A-Z])/', '\\1_\\2', $tmp);
    $tmp = preg_replace('/([a-z])([A-Z])/', '\\1_\\2', $tmp);

    return strtolower($tmp);
  }

  public static function demodulize($class_name_in_module)
  {
    return preg_replace('/^.*::/', '', $class_name_in_module);
  }

  public static function foreign_key($class_name, $separate_class_name_and_id_with_underscore = true)
  {
    return sfInflector::underscore(sfInflector::demodulize($class_name)).($separate_class_name_and_id_with_underscore ? "_id" : "id");
  }

  public static function tableize($class_name)
  {
    return sfInflector::underscore($class_name);
  }

  public static function classify($table_name)
  {
    return sfInflector::camelize($table_name);
  }

  public static function humanize($lower_case_and_underscored_word)
  {
    return ucfirst(preg_replace('/_/', ' ', $lower_case_and_underscored_word));
  }
}

?>
