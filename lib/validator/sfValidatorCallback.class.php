<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCallback validates an input value if the given callback does not throw a sfValidatorError.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorCallback extends sfValidator
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * callback: A valid PHP callback
   *
   * @see sfValidator
   */
  public function __construct($options = array(), $messages = array())
  {
    if (!isset($options['callback']))
    {
      throw new sfException('The "callback" option is mandatory.');
    }

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->options['required'] = false;
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    return call_user_func($this->getOption('callback'), $this, $value);
  }

  /**
   * @see sfValidator
   */
  protected function getOptionsWithoutDefaults()
  {
    return parent::getOptionsWithoutDefaults(array('callback' => array('--fake--')));
  }

  /**
   * @see sfValidator
   */
  protected function getMessagesWithoutDefaults()
  {
    return parent::getMessagesWithoutDefaults(array('callback' => array('--fake--')));
  }
}
