<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorChoiceMany validates than an array of values is in the array of the expected values.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorChoiceMany extends sfValidatorChoice
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * min: The minimum number of values that need to be selected
   *  * max: The maximum number of values that need to be selected
   *
   * @see sfValidatorChoice
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addMessage('min', '%min% values must be selected (%count% values selected).');
    $this->addMessage('max', '%max% values must be selected (%count% values selected).');

    $this->addOption('min');
    $this->addOption('max');

    $this->setOption('multiple', true);
  }

  protected function doClean($value)
  {
    $value = parent::doClean($value);
    $count = is_array($value) ? count($value) : 1;

    if ($this->hasOption('min') && $count < $this->getOption('min'))
    {
      throw new sfValidatorError($this, 'min', array('count' => $count, 'min' => $this->getOption('min')));
    }

    if ($this->hasOption('max') && $count > $this->getOption('max'))
    {
      throw new sfValidatorError($this, 'max', array('count' => $count, 'max' => $this->getOption('max')));
    }

    return $value;
  }
}
