<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSelectRadio represents radio HTML tags.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormSelectRadio extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:         An array of possible choices (required)
   *  * label_separator: The separator to use between the input radio and the label
   *  * separator:       The separator to use between each input radio
   *  * formatter:       A callable to call to format the radio choices
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *   
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    $this->addOption('label_separator', '&nbsp;');
    $this->addOption('separator', "\n");
    $this->addOption('formatter', array($this, 'formatter'));
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $inputs = array();
    foreach ($choices as $key => $option)
    {
      $baseAttributes = array(
        'name'  => $name,
        'type'  => 'radio',
        'value' => self::escapeOnce($key),
        'id'    => $id = $this->generateId($name.'[]', self::escapeOnce($key)),
      );

      if (strval($key) == strval($value))
      {
        $baseAttributes['checked'] = 'checked';
      }

      $inputs[] = array(
        'input' => $this->renderTag('input', array_merge($baseAttributes,$attributes)),
        'label' => $this->renderContentTag('label', $option, array('for' => $id)),
      );
    }

    return call_user_func($this->getOption('formatter'), $inputs);
  }

  public function formatter($inputs)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $this->renderContentTag('li', $input['input'].$this->getOption('label_separator').$input['label']);
    }

    return $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), array('class' => 'radio_list'));
  }
}
