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
   * Constructor.
   *
   * Available options:
   *
   *  * choices: An array of expected values
   *
   * @see sfValidator
   */
  public function __construct($options = array(), $messages = array())
  {
    if (!isset($options['choices']))
    {
      throw new sfException('The "choices" option is mandatory.');
    }

    parent::__construct($options, $messages);
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

  /**
   * @see sfValidator
   */
  protected function getOptionsWithoutDefaults()
  {
    return parent::getOptionsWithoutDefaults(array('choices' => array('--fake--')));
  }

  /**
   * @see sfValidator
   */
  protected function getMessagesWithoutDefaults()
  {
    return parent::getMessagesWithoutDefaults(array('choices' => array('--fake--')));
  }
}
