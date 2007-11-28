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
class sfFormField implements ArrayAccess
{
  protected
    $widget = null,
    $parent = null,
    $name   = '',
    $value  = null,
    $error  = null,
    $fields = array();

  /**
   * Constructor.
   *
   * @param sfWidget         A sfWidget instance
   * @param sfFormField      The sfFormField parent instance (null for the root widget)
   * @param string           The field name
   * @param string           The field value
   * @param sfValidatorError A sfValidatorError instance
   */
  public function __construct(sfWidget $widget, sfFormField $parent = null, $name, $value, sfValidatorError $error = null)
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
   * @param  array  An array of HTML attributes
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
   * the help message if given.
   *
   * @param  string The help text
   *
   * @return string The formatted row
   */
  public function renderRow($help = '')
  {
    if ($this->widget instanceof sfWidgetFormSchema)
    {
      throw new LogicException('Unable to format a row on a sfWidgetFormSchema.');
    }

    $field = $this->parent->getWidget()->renderField($this->name, $this->value, $this->error);

    return strtr($this->parent->getWidget()->getFormFormatter()->formatRow($this->renderLabel(), $field, $this->error, $help), array('%hidden_fields%' => ''));
  }

  /**
   * Returns a formatted error list.
   *
   * The formatted list will use the parent widget schema formatter.
   *
   * @param  string The widget name
   *
   * @return string The formatted error list
   */
  public function renderError()
  {
    if ($this->widget instanceof sfWidgetFormSchema)
    {
      throw new LogicException('Unable to format an error list on a sfWidgetFormSchema.');
    }

    return $this->parent->getWidget()->getFormFormatter()->formatErrorsForRow($this->error);
  }

  /**
   * Returns the label tag.
   *
   * @return string The label tag
   */
  public function renderLabel()
  {
    if ($this->widget instanceof sfWidgetFormSchema)
    {
      throw new LogicException('Unable to render a label on a sfWidgetFormSchema.');
    }

    return $this->parent->getWidget()->generateLabel($this->name);
  }

  /**
   * Returns the label name given a widget name.
   *
   * @return string The label name
   */
  public function renderLabelName()
  {
    if ($this->widget instanceof sfWidgetFormSchema)
    {
      throw new LogicException('Unable to render a label name on a sfWidgetFormSchema.');
    }

    return $this->parent->getWidget()->generateLabelName($this->name);
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

  /**
   * Returns true if the bound field exists (implements the ArrayAccess interface).
   *
   * @param  string  The name of the bound field
   *
   * @return Boolean true if the widget exists, false otherwise
   */
  public function offsetExists($name)
  {
    return $this->widget instanceof sfWidgetFormSchema ? isset($this->widget[$name]) : false;
  }

  /**
   * Returns the form field associated with the name (implements the ArrayAccess interface).
   *
   * @param  string      The offset of the value to get
   *
   * @return sfFormField A form field instance
   */
  public function offsetGet($name)
  {
    if (!isset($this->fields[$name]))
    {
      if (!$this->widget instanceof sfWidgetFormSchema)
      {
        throw new LogicException(sprintf('Cannot get a form field on a non widget schema (%s given).', get_class($this->widget)));
      }

      if (is_null($widget = $this->widget[$name]))
      {
        throw new InvalidArgumentException(sprintf('Widget "%s" does not exist.', $name));
      }

      $this->fields[$name] = new sfFormField($widget, $this, $name, isset($this->value[$name]) ? $this->value[$name] : null, isset($this->error[$name]) ? $this->error[$name] : null);
    }

    return $this->fields[$name];
  }

  /**
   * Throws an exception saying that values cannot be set (implements the ArrayAccess interface).
   *
   * @param string (ignored)
   * @param string (ignored)
   *
   * @throws <b>LogicException</b>
   */
  public function offsetSet($offset, $value)
  {
    throw new LogicException('Cannot update form fields (read-only).');
  }

  /**
   * Throws an exception saying that values cannot be unset (implements the ArrayAccess interface).
   *
   * @param string (ignored)
   *
   * @throws LogicException
   */
  public function offsetUnset($offset)
  {
    throw new LogicException('Cannot remove form fields (read-only).');
  }
}
