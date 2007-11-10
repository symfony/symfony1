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
   *  * separator: Separator between the date and the time widget (a space by default)
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('date', array());
    $this->addOption('time', array());
    $this->addOption('with_time', true);
    $this->addOption('separator', ' ');
  }

  /**
   * @see sfWidgetForm
   */
  function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // date
    $options = $this->getOptionsFor('date');
    $attributes = $this->getAttributesFor($options, $attributes);
    unset($options['attributes']);
    $date = new sfWidgetFormDate($options, $attributes);
    $html = $date->render($name, $value);

    // time
    if ($this->getOption('with_time'))
    {
      $options = $this->getOptionsFor('time');
      $attributes = $this->getAttributesFor($options, $attributes);
      unset($options['attributes']);
      $time = new sfWidgetFormTime($options, $attributes);

      $html .= $this->getOption('separator').$time->render($name, $value);
    }

    return $html;
  }

  protected function getOptionsFor($type)
  {
    $options = $this->getOption($type);
    if (!is_array($options))
    {
      throw new sfException(sprintf('You must pass an array for the %s option.', $type));
    }

    return $options;
  }

  protected function getAttributesFor($options, $attributes)
  {
    $attributes = array_merge($this->attributes, $attributes);
    if (isset($options['attributes']))
    {
      $attributes = array_merge($attributes, $options['attributes']);
    }

    return $attributes;
  }
}
