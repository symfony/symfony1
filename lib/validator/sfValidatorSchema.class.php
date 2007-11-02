<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSchema represents an array of fields.
 *
 * A field is a named validator.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorSchema extends sfValidator implements ArrayAccess
{
  protected
    $fields = array();

  /**
   * Constructor.
   *
   * The first argument can be:
   *
   *  * null
   *  * an array of named sfValidator instances
   *
   * @param mixed Initial fields
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($fields = null, $options = array(), $messages = array())
  {
    if (is_array($fields))
    {
      foreach ($fields as $name => $validator)
      {
        $this[$name] = $validator;
      }
    }
    else if (!is_null($fields))
    {
      throw new sfException('sfValidatorSchema constructor takes an array of sfValidator objects.');
    }

    parent::__construct($options, $messages);
  }

  /**
   * Configures the validator.
   *
   * Available options:
   *
   *  * allow_extra_fields:  if false, the validator adds an error if extra fields are given in the input array of values (default to false)
   *  * filter_extra_fields: if true, the validator filters extra fields from the returned array of cleaned values (default to true)
   *
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see sfValidator
   */
  public function configure($options = array(), $messages = array())
  {
    $this->setOption('allow_extra_fields', false);
    $this->setOption('filter_extra_fields', true);

    $this->setMessage('extra_fields', 'Extra field %field%.');
  }

  /**
   * @see sfValidator
   */
  public function clean($values)
  {
    return $this->doClean($values);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($values)
  {
    if (is_null($values))
    {
      $values = array();
    }

    if (!is_array($values))
    {
      throw new sfException('You must pass an array parameter to the clean() method');
    }

    $clean = array();
    $unused = array_keys($this->fields);
    $errors = array();

    // pre validator
    if (isset($this->fields['_pre_validator']))
    {
      try
      {
        $this->fields['_pre_validator']->clean($values);
      }
      catch (sfValidatorError $e)
      {
        $errors[] = $e;
      }
    }

    // validate given values
    foreach ($values as $name => $value)
    {
      // field exists in our schema?
      if (!array_key_exists($name, $this->fields))
      {
        if (!$this->options['allow_extra_fields'])
        {
          $errors[] = new sfValidatorError($this, 'extra_fields', array('field' => $name));
        }
        else if (!$this->options['filter_extra_fields'])
        {
          $clean[$name] = $value;
        }

        continue;
      }

      unset($unused[array_search($name, $unused)]);

      // validate value
      try
      {
        $clean[$name] = $this->fields[$name]->clean($value);
      }
      catch (sfValidatorError $e)
      {
        $clean[$name] = null;

        $errors[$name] = $e;
      }
    }

    // are non given values required?
    foreach ($unused as $name)
    {
      if (in_array($name, array('_pre_validator', '_post_validator')))
      {
        continue;
      }

      // validate value
      try
      {
        $this->fields[$name]->clean(null);
      }
      catch (sfValidatorError $e)
      {
        $errors[$name] = $e;
      }
    }

    // post validator
    if (isset($this->fields['_post_validator']))
    {
      try
      {
        $clean = $this->fields['_post_validator']->clean($clean);
      }
      catch (sfValidatorError $e)
      {
        $errors[] = $e;
      }
    }

    if (count($errors))
    {
      throw new sfValidatorErrorSchema($this, $errors);
    }

    return $clean;
  }

  /**
   * Returns true if the schema has a field with the given name (implements the ArrayAccess interface).
   *
   * @param  string  The field name
   *
   * @return Boolean true if the schema has a field with the given name, false otherwise
   */
  public function offsetExists($name)
  {
    return isset($this->fields[$name]);
  }

  /**
   * Gets the field associated with the given name (implements the ArrayAccess interface).
   *
   * @param  string   The field name
   *
   * @return sfValidator The sfValidator instance associated with the given name, null if it does not exist
   */
  public function offsetGet($name)
  {
    return isset($this->fields[$name]) ? $this->fields[$name] : null;
  }

  /**
   * Sets a field (implements the ArrayAccess interface).
   *
   * @param string      The field name
   * @param sfValidator A sfValidator instance
   */
  public function offsetSet($name, $validator)
  {
    if (!$validator instanceof sfValidator)
    {
      throw new sfException('A field must be an instance of sfValidator.');
    }

    $this->fields[$name] = $validator;
  }

  /**
   * Removes a field by name (implements the ArrayAccess interface).
   *
   * @param string
   */
  public function offsetUnset($name)
  {
    unset($this->fields[$name]);
  }

  /**
   * Returns an array of fields.
   *
   * @return sfValidator An array of sfValidator instance
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * @see sfValidator
   */
  public function asString($indent = 0)
  {
    throw new sfException('Unable to convert a sfValidatorSchema to string.');
  }
}
