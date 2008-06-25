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
}
