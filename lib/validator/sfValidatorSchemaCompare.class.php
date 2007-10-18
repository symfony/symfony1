<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSchemaCompare compares several values from an array.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorSchemaCompare extends sfValidatorSchema
{
  const EQUAL              = 'equal';
  const NOT_EQUAL          = 'not_equal';
  const LESS_THAN          = 'less_than';
  const LESS_THAN_EQUAL    = 'less_than_equal';
  const GREATER_THAN       = 'greater_than';
  const GREATER_THAN_EQUAL = 'greater_than_equal';

  /**
   * Constructor.
   *
   * Available operator:
   *
   *  * self::EQUAL
   *  * self::NOT_EQUAL
   *  * self::LESS_THAN
   *  * self::LESS_THAN_EQUAL
   *  * self::GREATER_THAN
   *  * self::GREATER_THAN_EQUAL
   *
   * @param string The left field name
   * @param string The operator to apply
   * @param string The right field name
   * @param array  An array of options
   * @param array  An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($leftField, $operator, $rightField, $options = array(), $messages = array())
  {
    $options['leftField']  = $leftField;
    $options['operator']   = $operator;
    $options['rightField'] = $rightField;

    parent::__construct(null, $options, $messages);
  }

  /**
   * @see sfValidator
   */
  public function clean($value)
  {
    return $this->doClean($value);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($values)
  {
    if (is_null($values))
    {
      $values = array();
    }

    if (!is_array($values))
    {
      throw new sfException('You must pass an array parameter to the clean() method');
    }

    $leftValue  = isset($values[$this->getOption('leftField')]) ? $values[$this->getOption('leftField')] : null;
    $rightValue = isset($values[$this->getOption('rightField')]) ? $values[$this->getOption('rightField')] : null;

    switch ($this->getOption('operator'))
    {
      case self::GREATER_THAN:
        $valid = $leftValue > $rightValue;
        break;
      case self::GREATER_THAN_EQUAL:
        $valid = $leftValue >= $rightValue;
        break;
      case self::LESS_THAN:
        $valid = $leftValue < $rightValue;
        break;
      case self::LESS_THAN_EQUAL:
        $valid = $leftValue <= $rightValue;
        break;
      case self::NOT_EQUAL:
        $valid = $leftValue != $rightValue;
        break;
      case self::EQUAL:
      default:
        $valid = $leftValue == $rightValue;
    }

    if (!$valid)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $values));
    }

    return $values;
  }
}
