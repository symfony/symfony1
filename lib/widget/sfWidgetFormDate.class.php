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
   *  * separator: Date separator (/ by default)
   *  * years:     An array of years for the year select tag (optional)
   *  * months:    An array of months for the month select tag (optional)
   *  * days:      An array of days for the day select tag (optional)
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('separator', '/');
    $this->addOption('days', array_combine(range(1, 31), range(1, 31)));
    $this->addOption('months', array_combine(range(1, 12), range(1, 12)));
    $years = range(date('Y') - 5, date('Y') + 5);
    $this->addOption('years', array_combine($years, $years));
  }

  /**
   * @see sfWidgetForm
   */
  function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // convert value to a timestamp
    $value = ctype_digit($value) ? (integer) $value : strtotime($value);

    $date = array();

    // days
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('days')));
    $date[] = $widget->render($name.'[day]', $value ? date('j', $value) : '');

    // months
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('months')));
    $date[] = $widget->render($name.'[month]', $value ? date('n', $value) : '');

    // years
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('years')));
    $date[] = $widget->render($name.'[year]', $value ? date('Y', $value) : '');

    return implode($this->getOption('separator'), $date);
  }
}
