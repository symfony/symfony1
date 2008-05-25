<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormField represents a widget bind to a name and a value.
 *
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFormField
{
  protected
    $widget = null,
    $parent = null,
    $name   = '',
    $value  = null,
    $error  = null;

  /**
   * Constructor.
   *
   * @param sfWidgetForm     $widget    A sfWidget instance
   * @param sfFormField      $parent    The sfFormField parent instance (null for the root widget)
   * @param string           $name      The field name
   * @param string           $value     The field value
   * @param sfValidatorError $error     A sfValidatorError instance
   */
  public function __construct(sfWidgetForm $widget, sfFormField $parent = null, $name, $value, sfValidatorError $error = null)
  {
    $this->widget = $widget;
    $this->parent = $parent;
    $this->name   = $name;
    $this->value  = $value;
    $this->error  = $error;
  }

  /**
   * Returns the string representation of this form field.
   *
   * @return string The rendered field
   */
  public function __toString()
  {
    return $this->render();
  }

  /**
   * Renders the form field.
   *
   * @param  array  $attributes   An array of HTML attributes
   *
   * @return string The rendered widget
   */
  function render($attributes = array())
  {
    return $this->widget->render($this->parent ? $this->parent->getWidget()->generateName($this->name) : $this->name, $this->value, $attributes, $this->error);
  }

  /**
   * Returns a formatted row.
   *
   * The formatted row will use the parent widget schema formatter.
   * The formatted row contains the label, the field, the error and
   * the help message.
   *
   * @param  array  $attributes   An array of HTML attributes to merge with the current attributes
   * @param  string $label        The label name (not null to override the current value)
   * @param  string $help         The help text (not null to override the current value)
   *
   * @return string The formatted row
   */
  public function renderRow($attributes = array(), $label = null, $help = null)
  {
    if (is_null($this->parent))
    {
      throw new LogicException(sprintf('Unable to render the row for "%s".', $this->name));
    }

    $field = $this->parent->getWidget()->renderField($this->name, $this->value, !is_array($attributes) ? array() : $attributes, $this->error);

    $error = $this->error instanceof sfValidatorErrorSchema ? $this->error->getGlobalErrors() : $this->error;

    $help = is_null($help) ? $this->parent->getWidget()->getHelp($this->name) : $help;

    return strtr($this->parent->getWidget()->getFormFormatter()->formatRow($this->renderLabel($label), $field, $error, $help), array('%hidden_fields%' => ''));
  }

  /**
   * Returns a formatted error list.
   *
   * The formatted list will use the parent widget schema formatter.
   *
   * @return string The formatted error list
   */
  public function renderError()
  {
    if (is_null($this->parent))
    {
      throw new LogicException(sprintf('Unable to render the error for "%s".', $this->name));
    }

    $error = $this->getWidget() instanceof sfWidgetFormSchema ? $this->getWidget()->getGlobalErrors($this->error) : $this->error;

    return $this->parent->getWidget()->getFormFormatter()->formatErrorsForRow($error);
  }

  /**
   * Returns the label tag.
   *
   * @param  string $label       The label name (not null to override the current value)
   * @param  array  $attributes  Optional html attributes
   *
   * @return string The label tag
   */
  public function renderLabel($label = null, $attributes = array())
  {
    if (is_null($this->parent))
    {
      throw new LogicException(sprintf('Unable to render the label for "%s".', $this->name));
    }

    if (!is_null($label))
    {
      $currentLabel = $this->parent->getWidget()->getLabel($this->name);
      $this->parent->getWidget()->setLabel($this->name, $label);
    }

    $html = $this->parent->getWidget()->getFormFormatter()->generateLabel($this->name, $attributes);

    if (!is_null($label))
    {
      $this->parent->getWidget()->setLabel($this->name, $currentLabel);
    }

    return $html;
  }

  /**
   * Returns the label name given a widget name.
   *
   * @return string The label name
   */
  public function renderLabelName()
  {
    if (is_null($this->parent))
    {
      throw new LogicException(sprintf('Unable to render the label name for "%s".', $this->name));
    }

    return $this->parent->getWidget()->getFormFormatter()->generateLabelName($this->name);
  }

  /**
   * Returns true if the widget is hidden.
   *
   * @return Boolean true if the widget is hidden, false otherwise
   */
  public function isHidden()
  {
    return $this->widget->isHidden();
  }

  /**
   * Returns the widget value.
   *
   * @return mixed The widget value
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Returns the wrapped widget.
   *
   * @return sfWidget A sfWidget instance
   */
  public function getWidget()
  {
    return $this->widget;
  }

  /**
   * Returns the parent form field.
   *
   * @return sfFormField A sfFormField instance
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Returns the error for this field.
   *
   * @return sfValidatorError A sfValidatorError instance
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * Returns true is the field has an error.
   *
   * @return Boolean true if the field has some errors, false otherwise
   */
  public function hasError()
  {
    return !is_null($this->error) && count($this->error);
  }
}
