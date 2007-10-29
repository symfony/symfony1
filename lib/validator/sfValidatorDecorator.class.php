<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDecorator decorates another validator.
 *
 * This validator has exactly the same behavior as the Decorator validator.
 *
 * The options and messages are proxied from the decorated validator.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfValidatorDecorator extends sfValidator
{
  protected
    $validator = null;

  /**
   * @see sfValidator
   */
  public function __construct($options = array(), $messages = array())
  {
    $this->validator = $this->getValidator();

    if (!$this->validator instanceof sfValidator)
    {
      throw new sfException('The getValidator() method must return a sfValidator instance.');
    }

    foreach ($options as $key => $value)
    {
      $this->validator->setOption($key, $value);
    }

    foreach ($messages as $key => $value)
    {
      $this->validator->setMessage($key, $value);
    }
  }

  /**
   * Returns the decorated validator.
   *
   * Every subclass must implement this method.
   *
   * @return sfValidator A sfValidator instance
   */
  abstract protected function getValidator();

  /**
   * @see sfValidator
   */
  public function clean($value)
  {
    return $this->doClean($value);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    return $this->validator->clean($value);
  }

  /**
   * @see sfValidator
   */
  public function getMessage($name)
  {
    return $this->validator->getMessage($name);
  }

  /**
   * @see sfValidator
   */
  public function setMessage($name, $value)
  {
    $this->validator->setMessage($name, $value);
  }

  /**
   * @see sfValidator
   */
  public function getMessages()
  {
    return $this->validator->getMessages();
  }

  /**
   * @see sfValidator
   */
  public function setMessages($values)
  {
    return $this->validator->setMessages($values);
  }

  /**
   * @see sfValidator
   */
  public function getOption($name)
  {
    return $this->validator->getOption($name);
  }

  /**
   * @see sfValidator
   */
  public function setOption($name, $value)
  {
    $this->validator->setOption($name, $value);
  }

  /**
   * @see sfValidator
   */
  public function hasOption($name)
  {
    return $this->validator->hasOption($name);
  }

  /**
   * @see sfValidator
   */
  public function getOptions()
  {
    return $this->validator->getOptions();
  }

  /**
   * @see sfValidator
   */
  public function setOptions($values)
  {
    $this->validator->setOptions($values);
  }

  /**
   * @see sfValidator
   */
  public function getErrorCodes()
  {
    return $this->getValidator()->getErrorCodes();
  }

  /**
   * @see sfValidator
   */
  public function asString($indent = 0)
  {
    return $this->getValidator()->asString($indent);
  }
}
