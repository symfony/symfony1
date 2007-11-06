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
   * Configures the current validator.
   *
   * Available options:
   *
   *  * callback: A valid PHP callback (required)
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('callback');

    $this->setOption('required', false);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    return call_user_func($this->getOption('callback'), $this, $value);
  }
}
