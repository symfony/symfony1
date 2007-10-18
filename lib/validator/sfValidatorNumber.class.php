<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorNumber validates a number (integer or float). It also converts the input value to a float.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorNumber extends sfValidator
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max: The maximum value allowed
   *  * min: The minimum value allowed
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('max', '"%value%" is too long (largest allowed is %max%).');
    $this->setMessage('min', '"%value%" is too short (smallest allowed is %min%).');
    $this->setMessage('invalid', '"%value%" is not a number.');
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    $clean = floatval($value);

    if (strval($clean) != $value)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    if ($this->hasOption('max') && $clean > $this->getOption('max'))
    {
      throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
    }

    if ($this->hasOption('min') && $clean < $this->getOption('min'))
    {
      throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
    }

    return $clean;
  }

  /**
   * @see sfValidator
   */
  public function getErrorCodes()
  {
    return array_merge(parent::getErrorCodes(), array('max', 'min'));
  }
}
