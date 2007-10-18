<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidator is the base class for all validators.
 *
 * It also implements the required option for all validators.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfValidator
{
  protected static
    $charset  = 'UTF-8';

  protected
    $messages = array(),
    $options  = array();

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * required:    true if the value is required, false otherwise (default to true)
   *  * trim:        true if the value must be trimmed, false otherwise (default to false)
   *  * empty_value: empty value when value is not required
   *
   * @param array An array of options
   * @param array An array of error messages
   */
  public function __construct($options = array(), $messages = array())
  {
    $this->messages = array('required' => 'Required.', 'invalid' => 'Invalid.');
    $this->options  = array('required' => true, 'trim' => false, 'empty_value' => null);

    $this->configure($options, $messages);

    $this->options  = array_merge($this->options, $options);
    $this->messages = array_merge($this->messages, $messages);
  }

  /**
   * Returns an array of current error messages.
   *
   * @return array An array of messages
   */
  public function getMessages()
  {
    return $this->messages;
  }

  /**
   * Changes an error message given the error code.
   *
   * @param string The error code
   * @param string The error message
   */
  public function setMessage($name, $value)
  {
    $this->messages[$name] = $value;
  }

  /**
   * Returns an error message given an error code.
   *
   * @param  string The error code
   *
   * @return string The error message, or the empty string if the error code does not exist
   */
  public function getMessage($name)
  {
    return isset($this->messages[$name]) ? $this->messages[$name] : '';
  }

  /**
   * Changes an option value.
   *
   * @param string The option name
   * @param mixed  The value
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  /**
   * Gets an option value.
   *
   * @param  string The option name
   *
   * @return mixed  The option value
   */
  public function getOption($name)
  {
    return isset($this->options[$name]) ? $this->options[$name] : null;
  }

  /**
   * Returns true if the option exists.
   *
   * @param  string  The option name
   *
   * @return Boolean true if the option exists, false otherwise
   */
  public function hasOption($name)
  {
    return isset($this->options[$name]);
  }

  /**
   * Cleans the input value.
   *
   * This method is also responsible for trimming the input value
   * and checking the required option.
   *
   * @param  mixed The input value
   *
   * @return mixed The cleaned value
   *
   * @throws sfValidatorError
   */
  public function clean($value)
  {
    $clean = $value;

    if ($this->options['trim'] && is_string($clean))
    {
      $clean = trim($clean);
    }

    // empty value?
    if ($this->isEmpty($clean))
    {
      // required?
      if ($this->options['required'])
      {
        throw new sfValidatorError($this, 'required');
      }

      return $this->getEmptyValue();
    }

    return $this->doClean($clean);
  }

  /**
   * Cleans the input value.
   *
   * Every subclass must implements this method.
   *
   * @param  mixed The input value
   *
   * @return mixed The cleaned value
   *
   * @throws sfValidatorError
   */
   abstract protected function doClean($value);

  /**
   * Returns an array of all error codes for this validator.
   *
   * Subclasses of sfValidator may override this method to register
   * their own error codes.
   *
   * By default this method return required and invalid as errors codes.
   *
   * @return array An array of possible error codes
   */
  public function getErrorCodes()
  {
    return array('required', 'invalid');
  }

  /**
   * Sets the charset to use when validating strings.
   *
   * @param string The charset
   */
  static public function setCharset($charset)
  {
    self::$charset = $charset;
  }

  /**
   * Returns the charset to use when validating strings.
   *
   * @return string The charset (default to UTF-8)
   */
  static public function getCharset()
  {
    return self::$charset;
  }

  /**
   * Returns true if the value is empty.
   *
   * @param  mixed   The input value
   *
   * @return Boolean true if the value is empty, false otherwise
   */
  protected function isEmpty($value)
  {
    return in_array($value, array(null, ''));
  }

  /**
   * Returns an empty value for this validator.
   *
   * @return mixed The empty value for this validator
   */
  protected function getEmptyValue()
  {
    return $this->getOption('empty_value');
  }

  /**
   * Configures the current validator.
   *
   * This method allows each validator to add options and error messages
   * during validator creation.
   *
   * If some options and messages are given in the sfValidator constructor
   * they will take precedence over the options and messages you configure
   * in this method.
   *
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see __construct()
   */
  protected function configure($options = array(), $messages = array())
  {
  }
}
