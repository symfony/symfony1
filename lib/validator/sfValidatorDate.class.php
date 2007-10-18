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
   *  * date_format:     A regular expression that dates must match
   *  * with_time:       true if the validator must return a time, false otherwise
   *  * date_output:     The format to use when returning a date (default to Y-m-d)
   *  * datetime_output: The format to use when returning a date with time (default to Y-m-d H:i:s)
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('bad_format', '"%value%" does not match the date format (%date_format%).');

    $this->setOption('with_time', false);
    $this->setOption('date_output', 'Y-m-d');
    $this->setOption('datetime_output', 'Y-m-d H:i:s');
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    if ($regex = $this->getOption('date_format'))
    {
      if (!preg_match($regex, $value, $match))
      {
        throw new sfValidatorError($this, 'bad_format', array('value' => $value, 'date_format' => $this->getOption('date_format_error') ? $this->getOption('date_format_error') : $this->getOption('date_format')));
      }

      if ($this->getOption('with_time'))
      {
        $clean = mktime(isset($match['hour']) ? $match['hour'] : 0, isset($match['minute']) ? $match['minute'] : 0, isset($match['second']) ? $match['second'] : 0, $match['month'], $match['day'], $match['year']);
      }
      else
      {
        $clean = mktime(0, 0, 0, $match['month'], $match['day'], $match['year']);
      }
    }
    else if (!ctype_digit($value))
    {
      $clean = strtotime($value);
      if ($clean === -1 || $clean === false)
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
   * @see sfValidator
   */
  public function getErrorCodes()
  {
    return array_merge(parent::getErrorCodes(), array('date_format'));
  }
}
