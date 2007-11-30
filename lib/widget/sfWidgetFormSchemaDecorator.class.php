<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaDecorator wraps a form schema widget inside a given HTML snippet.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormSchemaDecorator extends sfWidgetFormSchema
{
  protected
    $widget    = null,
    $decorator = '';

  /**
   * Constructor.
   *
   * The decorated widget is cloned.
   *
   * @param sfWidgetFormSchema A sfWidgetFormSchema instance
   * @param string             A decorator string
   *
   * @see sfWidgetFormSchema
   */
  public function __construct(sfWidgetFormSchema $widget, $decorator)
  {
    $this->widget    = clone $widget;
    $this->decorator = $decorator;

    $this->nameFormat     = $widget->getNameFormat();
    $this->formFormatters = $widget->getFormFormatters();
    $this->formFormatter  = $widget->getFormFormatterName();

    $this->attributes = $widget->getAttributes();
    $this->options    = $widget->getOptions();
    $this->labels     = $widget->getLabels();
    $this->helps      = $widget->getHelps();
  }

  /**
   * @see sfWidget
   */
  public function render($name, $values = array(), $attributes = array(), $errors = array())
  {
    $this->widget->setNameFormat($this->nameFormat);
    foreach ($this->formFormatters as $name => $formFormatter)
    {
      $this->widget->addFormFormatter($name, $formFormatter);
    }
    $this->widget->setFormFormatterName($this->formFormatter);

    $this->widget->setAttributes($this->attributes);
    $this->widget->setOptions($this->options);
    $this->widget->setLabels($this->labels);
    $this->widget->setHelps($this->helps);

    return strtr($this->decorator, array('%content%' => $this->widget->render($name, $values, $attributes, $errors)));
  }

  /**
   * @see sfWidgetFormSchema
   */
  public function offsetExists($name)
  {
    return isset($this->widget[$name]);
  }

  /**
   * @see sfWidgetFormSchema
   */
  public function offsetGet($name)
  {
    return $this->widget[$name];
  }

  /**
   * @see sfWidgetFormSchema
   */
  public function offsetSet($name, $widget)
  {
    $this->widget[$name] = $widget;
  }

  /**
   * @see sfWidgetFormSchema
   */
  public function offsetUnset($name)
  {
    unset($this->widget[$name]);
  }

  /**
   * @see sfWidgetFormSchema
   */
  public function getFields()
  {
    return $this->widget->getFields();
  }
}
