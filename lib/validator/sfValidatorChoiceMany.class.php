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
class sfValidatorChoiceMany extends sfValidator
{
  /**
   * Constructor.
   *
   * @param array  An array of expected values
   * @param array  An array of options
   * @param array  An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($expected, $options = array(), $messages = array())
  {
    $options['expected'] = $expected;

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    foreach ($values as $value)
    {
      if (!in_array($value, $this->getOption('expected')))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    return $values;
  }
}
