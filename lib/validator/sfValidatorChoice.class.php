<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorChoice validates than the value is one of the expected values.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorChoice extends sfValidator
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * choices: An array of expected values (required)
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('choices');
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    if (!in_array($value, $this->getOption('choices')))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $value;
  }
}
