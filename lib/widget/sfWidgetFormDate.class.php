<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDate represents a date widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormDate extends sfWidgetForm
{
  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * format:       The date format string (%month%/%day%/%year% by default)
   *  * years:        An array of years for the year select tag (optional)
   *  * months:       An array of months for the month select tag (optional)
   *  * days:         An array of days for the day select tag (optional)
   *  * can_be_empty: Whether the widget accept an empty value (true by default)
   *  * empty_values: An array of values to use for the empty value (empty string for year, month, and date by default)
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('format', '%month%/%day%/%year%');
    $this->addOption('days', array_combine(range(1, 31), range(1, 31)));
    $this->addOption('months', array_combine(range(1, 12), range(1, 12)));
    $years = range(date('Y') - 5, date('Y') + 5);
    $this->addOption('years', array_combine($years, $years));

    $this->addOption('can_be_empty', true);
    $this->addOption('empty_values', array('year' => '', 'month' => '', 'day' => ''));
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

    $date = array();
    $emptyValues = $this->getOption('empty_values');

    // days
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['day']) + $this->getOption('days') : $this->getOption('days')));
    $date['%day%'] = $widget->render($name.'[day]', $value ? date('j', $value) : '');

    // months
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['month']) + $this->getOption('months') : $this->getOption('months')));
    $date['%month%'] = $widget->render($name.'[month]', $value ? date('n', $value) : '');

    // years
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['year']) + $this->getOption('years') : $this->getOption('years')));
    $date['%year%'] = $widget->render($name.'[year]', $value ? date('Y', $value) : '');

    return strtr($this->getOption('format'), $date);
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
    $clean = mktime(isset($value['hour']) ? $value['hour'] : 0, isset($value['minute']) ? $value['minute'] : 0, isset($value['second']) ? $value['second'] : 0, $value['month'], $value['day'], $value['year']);

    return false === $clean ? null : $clean;
  }
}
