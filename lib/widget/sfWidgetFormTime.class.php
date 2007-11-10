<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTime represents a time widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormTime extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * with_second: Whether to include a select for seconds (false by default)
   *  * separator:   Time separator (: by default)
   *  * hours:       An array of hours for the hour select tag (optional)
   *  * minutes:     An array of minutes for the minute select tag (optional)
   *  * seconds:     An array of seconds for the second select tag (optional)
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('with_seconds', false);
    $this->addOption('separator', ':');
    $this->addOption('hours', array_combine(range(0, 23), range(0, 23)));
    $this->addOption('minutes', array_combine(range(0, 59), range(0, 59)));
    $this->addOption('seconds', array_combine(range(0, 59), range(0, 59)));
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // convert value to a timestamp
    if (is_array($value))
    {
      $value = $this->convertDateArrayToTimestamp($value);
    }
    else
    {
      $value = ctype_digit($value) ? (integer) $value : strtotime($value);
    }

    $time = array();

    // hours
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('hours')));
    $time[] = $widget->render($name.'[hour]', $value ? date('G', $value) : '');

    // minutes
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('minutes')));
    $time[] = $widget->render($name.'[minute]', $value ? date('i', $value) : '');

    if ($this->getOption('with_seconds'))
    {
      // seconds
      $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('seconds')));
      $time[] = $widget->render($name.'[second]', $value ? date('s', $value) : '');
    }

    return implode($this->getOption('separator'), $time);
  }

  /**
   * Converts an array representing a date to a timestamp.
   *
   * The array can contains the following keys: hour, minute, second
   *
   * @param  array   An array of date elements
   *
   * @return integer A timestamp
   */
  protected function convertDateArrayToTimestamp($value)
  {
    $clean = mktime(isset($value['hour']) ? $value['hour'] : 0, isset($value['minute']) ? $value['minute'] : 0, isset($value['second']) ? $value['second'] : 0);

    return false === $clean ? null : $clean;
  }
}
