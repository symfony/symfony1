<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDate validates a date. It also converts the input value to a valid date.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorDate extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * date_format:       A regular expression that dates must match
   *  * with_time:         true if the validator must return a time, false otherwise
   *  * date_output:       The format to use when returning a date (default to Y-m-d)
   *  * datetime_output:   The format to use when returning a date with time (default to Y-m-d H:i:s)
   *  * date_format_error: The date format to use when displaying an error for a bad_format error
   *
   * Available error codes:
   *
   *  * bad_format
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('bad_format', '"%value%" does not match the date format (%date_format%).');

    $this->addOption('date_format', null);
    $this->addOption('with_time', false);
    $this->addOption('date_output', 'Y-m-d');
    $this->addOption('datetime_output', 'Y-m-d H:i:s');
    $this->addOption('date_format_error');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if (is_array($value))
    {
      $clean = $this->convertDateArrayToTimestamp($value);
    }
    else if ($regex = $this->getOption('date_format'))
    {
      if (!preg_match($regex, $value, $match))
      {
        throw new sfValidatorError($this, 'bad_format', array('value' => $value, 'date_format' => $this->getOption('date_format_error') ? $this->getOption('date_format_error') : $this->getOption('date_format')));
      }

      $clean = $this->convertDateArrayToTimestamp($match);
    }
    else if (!ctype_digit($value))
    {
      $clean = strtotime($value);
      if (false === $clean)
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }
    else
    {
      $clean = (integer) $value;
    }

    return $clean === $this->getEmptyValue() ? $clean : date($this->getOption('with_time') ? $this->getOption('datetime_output') : $this->getOption('date_output'), $clean);
  }

  /**
   * Converts an array representing a date to a timestamp.
   *
   * The array can contains the following keys: year, month, day, hour, minute, second
   *
   * @param  array   An array of date elements
   *
   * @return integer A timestamp
   */
  protected function convertDateArrayToTimestamp($value)
  {
    // all elements must be empty or a number
    foreach (array('year', 'month', 'day', 'hour', 'minute', 'second') as $key)
    {
      if (isset($value[$key]) && !preg_match('#^\d+$#', $value[$key]) && !empty($value[$key]))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    // if one date value is empty, all others must be empty too
    $empties =
      (!isset($value['year']) || !$value['year'] ? 1 : 0) +
      (!isset($value['month']) || !$value['month'] ? 1 : 0) +
      (!isset($value['day']) || !$value['day'] ? 1 : 0)
    ;
    if ($empties > 0 && $empties < 3)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    else if (3 == $empties)
    {
      return $this->getEmptyValue();
    }

    if (!checkdate(intval($value['month']), intval($value['day']), intval($value['year'])))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    if ($this->getOption('with_time'))
    {
      // if one time value is empty, all others must be empty too
      // but second can be empty
      $empties =
        (!isset($value['hour']) || !$value['hour'] ? 1 : 0) +
        (!isset($value['minute']) || !$value['minute'] ? 1 : 0) +
        (!isset($value['second']) || !$value['second'] ? 1 : 0)
      ;
      if ($empties > 0 && $empties < 3)
      {
        if (1 == $empties && !isset($value['second']) || empty($value['second']))
        {
          // OK
        }
        else
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }
      }

      // if minute is not empty, hour cannot be empty
      $clean = mktime(
        isset($value['hour']) ? intval($value['hour']) : 0,
        isset($value['minute']) ? intval($value['minute']) : 0,
        isset($value['second']) ? intval($value['second']) : 0,
        intval($value['month']),
        intval($value['day']),
        intval($value['year'])
      );
    }
    else
    {
      $clean = mktime(0, 0, 0, intval($value['month']), intval($value['day']), intval($value['year']));
    }

    if (false === $clean)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => var_export($value, true)));
    }

    return $clean;
  }

  /**
   * @see sfValidatorBase
   */
  protected function isEmpty($value)
  {
    if (is_array($value))
    {
      $filtered = array_filter($value);

      return empty($filtered);
    }

    return parent::isEmpty($value);
  }
}
