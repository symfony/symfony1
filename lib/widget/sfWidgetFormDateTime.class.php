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
    $date = new sfWidgetFormDate($this->getOptionsFor('date'), $this->getAttributesFor('date'));

    if (!$this->getOption('with_time'))
    {
      return $date->render($name, $value);
    }

    $dateTime = array('%date%' => $date->render($name, $value));

    // time
    $time = new sfWidgetFormTime($this->getOptionsFor('time'), $this->getAttributesFor('time'));

    $dateTime['%time%'] = $time->render($name, $value);

    return strtr($this->getOption('format'), $dateTime);
  }

  protected function getOptionsFor($type)
  {
    $options = $this->getOption($type);
    if (!is_array($options))
    {
      throw new InvalidArgumentException(sprintf('You must pass an array for the %s option.', $type));
    }

    return $options;
  }

  protected function getAttributesFor($type)
  {
    return isset($attributes[$type]) ? array_merge($this->defaultAttributes[$type], $attributes[$type]) : $this->defaultAttributes[$type];
  }
}
