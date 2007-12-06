<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDateTime represents a datetime widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormDateTime extends sfWidgetForm
{
  protected
    $defaultAttributes = array('date' => array(), 'time' => array());

  /**
   * Configures the current widget.
   *
   * The attributes are passed to both the date and the time widget.
   *
   * If you want to pass HTML attributes to one of the two widget, pass an
   * attributes option to the date or time option (see below).
   *
   * Available options:
   *
   *  * date:      Options for the date widget (see sfWidgetFormDate)
   *  * time:      Options for the time widget (see sfWidgetFormTime)
   *  * with_time: Whether to include time (true by default)
   *  * format:    The format string for the date and the time widget (default to %date% %time%)
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('date', array());
    $this->addOption('time', array());
    $this->addOption('with_time', true);
    $this->addOption('format', '%date% %time%');

    if (isset($attributes['date']))
    {
      $defaultAttributes['time'] = $attributes['date'];
      unset($attributes['date']);
    }

    if (isset($attributes['time']))
    {
      $defaultAttributes['time'] = $attributes['time'];
      unset($attributes['time']);
    }
  }

  /**
   * @see sfWidgetForm
   */
  function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // date
    $date = $this->getDateWidget()->render($name, $value);

    if (!$this->getOption('with_time'))
    {
      return $date;
    }

    return strtr($this->getOption('format'), array(
      '%date%' => $date,
      '%time%' => $this->getTimeWidget()->render($name, $value),
    ));
  }

  /**
   * Returns the date widget
   *
   * @return sfWidgetForm A Widget representing the date
   */
  protected function getDateWidget()
  {
    return new sfWidgetFormDate($this->getOptionsFor('date'), $this->getAttributesFor('date'));
  }

  /**
   * Returns the time widget
   *
   * @return sfWidgetForm A Widget representing the time
   */
  protected function getTimeWidget()
  {
    return new sfWidgetFormTime($this->getOptionsFor('time'), $this->getAttributesFor('time'));
  }

  /**
   * Returns an array of options for the given type
   *
   * @param  string The type (date or time)
   *
   * @return array  An array of options
   */
  protected function getOptionsFor($type)
  {
    $options = $this->getOption($type);
    if (!is_array($options))
    {
      throw new InvalidArgumentException(sprintf('You must pass an array for the %s option.', $type));
    }

    return $options;
  }

  /**
   * Returns an array of HTML attributes for the given type
   *
   * @param  string The type (date or time)
   *
   * @return array  An array of HTML attributes
   */
  protected function getAttributesFor($type)
  {
    return isset($attributes[$type]) ? array_merge($this->defaultAttributes[$type], $attributes[$type]) : $this->defaultAttributes[$type];
  }
}
