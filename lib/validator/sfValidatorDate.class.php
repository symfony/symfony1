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
class sfValidatorDate extends sfValidator
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
   * @see sfValidator
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
   * @see sfValidator
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

    return date($this->getOption('with_time') ? $this->getOption('datetime_output') : $this->getOption('date_output'), $clean);
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
    if ($this->getOption('with_time'))
    {
      $clean = mktime(isset($value['hour']) ? $value['hour'] : 0, isset($value['minute']) ? $value['minute'] : 0, isset($value['second']) ? $value['second'] : 0, $value['month'], $value['day'], $value['year']);
    }
    else
    {
      $clean = mktime(0, 0, 0, $value['month'], $value['day'], $value['year']);
    }

    if (false === $clean)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => var_export($value, true)));
    }

    return $clean;
  }
}
