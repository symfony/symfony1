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
    $fields        = array(),
    $preValidator  = null,
    $postValidator = null;

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
      throw new InvalidArgumentException('sfValidatorSchema constructor takes an array of sfValidator objects.');
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
   * Available error codes:
   *
   *  * extra_fields
   *
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addOption('allow_extra_fields', false);
    $this->addOption('filter_extra_fields', true);

    $this->addMessage('extra_fields', 'Extra field %field%.');
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
      throw new InvalidArgumentException('You must pass an array parameter to the clean() method');
    }

    $clean  = array();
    $unused = array_keys($this->fields);
    $errorSchema = new sfValidatorErrorSchema($this);

    // pre validator
    try
    {
      $this->preClean($values);
    }
    catch (sfValidatorErrorSchema $e)
    {
      $errorSchema->addErrors($e);
    }
    catch (sfValidatorError $e)
    {
      $errorSchema->addError($e);
    }

    // validate given values
    foreach ($values as $name => $value)
    {
      // field exists in our schema?
      if (!array_key_exists($name, $this->fields))
      {
        if (!$this->options['allow_extra_fields'])
        {
          $errorSchema->addError(new sfValidatorError($this, 'extra_fields', array('field' => $name)));
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

        $errorSchema->addError($e, (string) $name);
      }
    }

    // are non given values required?
    foreach ($unused as $name)
    {
      // validate value
      try
      {
        $clean[$name] = $this->fields[$name]->clean(null);
      }
      catch (sfValidatorError $e)
      {
        $errorSchema->addError($e, (string) $name);
      }
    }

    // post validator
    try
    {
      $clean = $this->postClean($clean);
    }
    catch (sfValidatorErrorSchema $e)
    {
      $errorSchema->addErrors($e);
    }
    catch (sfValidatorError $e)
    {
      $errorSchema->addError($e);
    }

    if (count($errorSchema))
    {
      throw $errorSchema;
    }

    return $clean;
  }

  /**
   * Cleans the input values.
   *
   * This method is the first validator executed by doClean().
   *
   * It executes the validator returned by getPreValidator()
   * on the global array of values.
   *
   * @param  array The input values
   *
   * @throws sfValidatorError
   */
  public function preClean($values)
  {
    if (is_null($validator = $this->getPreValidator()))
    {
      return;
    }

    $validator->clean($values);
  }

  /**
   * Cleans the input values.
   *
   * This method is the last validator executed by doClean().
   *
   * It executes the validator returned by getPostValidator()
   * on the global array of cleaned values.
   *
   * @param  array The input values
   *
   * @throws sfValidatorError
   */
  public function postClean($values)
  {
    if (is_null($validator = $this->getPostValidator()))
    {
      return $values;
    }

    return $validator->clean($values);
  }

  /**
   * Sets the pre validator.
   *
   * @param sfValidator A sfValidator instance
   */
  public function setPreValidator(sfValidator $validator)
  {
    $this->preValidator = $validator;
  }

  /**
   * Returns the pre validator.
   *
   * @return sfValidator A sfValidator instance
   */
  public function getPreValidator()
  {
    return $this->preValidator;
  }

  /**
   * Sets the post validator.
   *
   * @param sfValidator A sfValidator instance
   */
  public function setPostValidator(sfValidator $validator)
  {
    $this->postValidator = $validator;
  }

  /**
   * Returns the post validator.
   *
   * @return sfValidator A sfValidator instance
   */
  public function getPostValidator()
  {
    return $this->postValidator;
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
      throw new InvalidArgumentException('A field must be an instance of sfValidator.');
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
    throw new Exception('Unable to convert a sfValidatorSchema to string.');
  }
}
