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
    $this->setOption('left_field', $leftField);
    $this->setOption('operator', $operator);
    $this->setOption('right_field', $rightField);

    parent::__construct(null, $options, $messages);
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

    $leftValue  = isset($values[$this->getOption('left_field')]) ? $values[$this->getOption('left_field')] : null;
    $rightValue = isset($values[$this->getOption('right_field')]) ? $values[$this->getOption('right_field')] : null;

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
      throw new sfValidatorError($this, 'invalid', array(
        'left_field'  => $leftValue,
        'right_field' => $rightValue,
        'operator'    => $this->getOption('operator'),
      ));
    }

    return $values;
  }

  /**
   * @see sfValidator
   */
  public function asString($indent = 0)
  {
    $options = $this->getOptionsWithoutDefaults();
    $messages = $this->getMessagesWithoutDefaults();
    unset($options['left_field'], $options['operator'], $options['right_field']);

    $arguments = '';
    if ($options || $messages)
    {
      $arguments = sprintf('(%s%s)',
        $options ? sfYamlInline::dump($options) : ($messages ? '{}' : ''),
        $messages ? ', '.sfYamlInline::dump($messages) : ''
      );
    }

    return sprintf('%s%s %s%s %s',
      str_repeat(' ', $indent),
      $this->getOption('left_field'),
      $this->getOption('operator'),
      $arguments,
      $this->getOption('right_field')
    );
  }
}
