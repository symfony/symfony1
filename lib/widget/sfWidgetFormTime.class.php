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
   *  * format:                 The time format string (%hour%:%minute%:%second%)
   *  * format_without_seconds: The time format string without seconds (%hour%:%minute%)
   *  * with_second:            Whether to include a select for seconds (false by default)
   *  * hours:                  An array of hours for the hour select tag (optional)
   *  * minutes:                An array of minutes for the minute select tag (optional)
   *  * seconds:                An array of seconds for the second select tag (optional)
   *  * can_be_empty:           Whether the widget accept an empty value (true by default)
   *  * empty_values:           An array of values to use for the empty value (empty string for year, month, and date by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('format', '%hour%:%minute%:%second%');
    $this->addOption('format_without_seconds', '%hour%:%minute%');
    $this->addOption('with_seconds', false);
    $this->addOption('hours', array_combine(range(0, 23), range(0, 23)));
    $this->addOption('minutes', array_combine(range(0, 59), range(0, 59)));
    $this->addOption('seconds', array_combine(range(0, 59), range(0, 59)));

    $this->addOption('can_be_empty', true);
    $this->addOption('empty_values', array('hour' => '', 'minute' => '', 'second' => ''));
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The time displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // convert value to an array
    $default = array('hour' => null, 'minute' => null, 'second' => null);
    if (is_array($value))
    {
      $value = array_merge($default, $value);
    }
    else
    {
      $value = ctype_digit($value) ? (integer) $value : strtotime($value);
      if (false === $value)
      {
        $value = $default;
      }
      else
      {
        $value = array('hour' => date('G', $value), 'minute' => date('i', $value), 'second' => date('s', $value));
      }
    }

    $time = array();
    $emptyValues = $this->getOption('empty_values');

    // hours
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['hour']) + $this->getOption('hours') : $this->getOption('hours')), array_merge($this->attributes, $attributes));
    $time['%hour%'] = $widget->render($name.'[hour]', $value['hour']);

    // minutes
    $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['minute']) + $this->getOption('minutes') : $this->getOption('minutes')), array_merge($this->attributes, $attributes));
    $time['%minute%'] = $widget->render($name.'[minute]', $value['minute']);

    if ($this->getOption('with_seconds'))
    {
      // seconds
      $widget = new sfWidgetFormSelect(array('choices' => $this->getOption('can_be_empty') ? array('' => $emptyValues['second']) + $this->getOption('seconds') : $this->getOption('seconds')), array_merge($this->attributes, $attributes));
      $time['%second%'] = $widget->render($name.'[second]', $value['second']);
    }

    return strtr($this->getOption('with_seconds') ? $this->getOption('format') : $this->getOption('format_without_seconds'), $time);
  }
}
