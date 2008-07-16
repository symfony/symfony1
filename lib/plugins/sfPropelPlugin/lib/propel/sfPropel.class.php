<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropel
{
  static protected
    $initialized    = false,
    $defaultCulture = 'en';

  static public function initialize(sfEventDispatcher $dispatcher, $culture = null)
  {
    $dispatcher->connect('user.change_culture', array('sfPropel', 'listenToChangeCultureEvent'));

    if (!is_null($culture))
    {
      self::setDefaultCulture($culture);
    }
    else if (class_exists('sfContext', false) && sfContext::hasInstance() && $user = sfContext::getInstance()->getUser())
    {
      self::setDefaultCulture($user->getCulture());
    }

    self::$initialized = true;
  }

  static public function setDefaultCulture($culture)
  {
    self::$defaultCulture = $culture;
  }

  static public function getDefaultCulture()
  {
    if (!self::$initialized && class_exists('sfProjectConfiguration', false))
    {
      self::initialize(sfProjectConfiguration::getActive()->getEventDispatcher());
    }

    return self::$defaultCulture;
  }

  /**
   * Listens to the user.change_culture event.
   *
   * @param sfEvent An sfEvent instance
   *
   */
  static public function listenToChangeCultureEvent(sfEvent $event)
  {
    self::setDefaultCulture($event['culture']);
  }

  /**
   * Include once a file specified in DOT notation and return unqualified classname.
   *
   * This method is the same as in Propel::import().
   * The only difference is that this one takes the autoloading into account.
   *
   * @see Propel::import()
   */
  public static function import($path)
  {
    // extract classname
    if (($pos = strrpos($path, '.')) === false)
    {
      $class = $path;
    }
    else
    {
      $class = substr($path, $pos + 1);
    }

    // check if class exists
    if (class_exists($class, true))
    {
      return $class;
    }

    // turn to filesystem path
    $path = strtr($path, '.', DIRECTORY_SEPARATOR).'.php';

    // include class
    $ret = include_once($path);
    if ($ret === false)
    {
      throw new PropelException("Unable to import class: ".$class." from ".$path);
    }

    // return qualified name
    return $class;
  }
}
