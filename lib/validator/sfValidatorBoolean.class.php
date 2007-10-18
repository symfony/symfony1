<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorBoolean validates a boolean. It also converts the input value to a valid boolean.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorBoolean extends sfValidator
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * true_values:  The list of true values
   *  * false_values: The list of false values
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
    $this->setOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    if (in_array($value, $this->getOption('true_values')))
    {
      return true;
    }

    if (in_array($value, $this->getOption('false_values')))
    {
      return false;
    }

    throw new sfValidatorError($this, 'invalid', array('value' => $value));
  }
}
