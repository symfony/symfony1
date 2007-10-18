<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorError represents a validation error.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorError extends Exception
{
  protected
    $validator = null,
    $arguments = array();

  /**
   * Constructor.
   *
   * @param sfValidator A sfValidator instance
   * @param string      The error code
   * @param array       An array of named arguments needed to render the error message
   */
  public function __construct(sfValidator $validator, $code, $arguments = array())
  {
    $this->validator = $validator;
    $this->arguments = $arguments;

    // override default exception message and code
    $this->code = $code;

    if (!$messageFormat = $this->getMessageFormat())
    {
      $messageFormat = $code;
    }
    $this->message = strtr($messageFormat, $this->getArguments());
  }

  /**
   * Returns the string representation of the error.
   *
   * @return string The error message
   */
  public function __toString()
  {
    return $this->getMessage();
  }

  /**
   * Returns the input value that triggered this error.
   *
   * @return mixed The input value
   */
  public function getValue()
  {
    return isset($this->arguments['value']) ? $this->arguments['value'] : null;
  }

  /**
   * Returns the validator that triggered this error.
   *
   * @return sfValidator A sfValidator instance
   */
  public function getValidator()
  {
    return $this->validator;
  }

  /**
   * Returns the arguments needed to format the message.
   *
   * @param Boolean false to use it as arguments for the message format, true otherwise (default to false)
   *
   * @see getMessageFormat()
   */
  public function getArguments($raw = false)
  {
    if ($raw)
    {
      return $this->arguments;
    }

    $arguments = array();
    foreach ($this->arguments as $key => $value)
    {
      $arguments["%$key%"] = $value;
    }

    return $arguments;
  }

  /**
   * Returns the message format for this error.
   *
   * This is the string you need to use if you need to internationalize
   * error messages:
   *
   * $i18n->__($error->getMessageFormat(), $error->getArguments());
   *
   * @return string The message format
   */
  public function getMessageFormat()
  {
    return $this->validator->getMessage($this->code);
  }
}
