<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorAll validates an input value if all validators passes.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorAll extends sfValidator
{
  protected
    $validators = array();

  /**
   * Constructor.
   *
   * The first argument can be:
   *
   *  * null
   *  * a sfValidator instance
   *  * an array of sfValidator instances
   *
   * @param mixed Initial validators
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($validators = null, $options = array(), $messages = array())
  {
    if ($validators instanceof sfValidator)
    {
      $this->addValidator($validators);
    }
    else if (is_array($validators))
    {
      foreach ($validators as $validator)
      {
        $this->addValidator($validator);
      }
    }
    else if (!is_null($validators))
    {
      throw new InvalidArgumentException('sfValidatorAll constructor takes a sfValidator object, or a sfValidator array.');
    }

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidator
   */
  public function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', null);
  }

  /**
   * Adds a validator.
   *
   * @param sfValidator A sfValidator instance
   */
  public function addValidator(sfValidator $validator)
  {
    $this->validators[] = $validator;
  }

  /**
   * Returns an array of the validators.
   *
   * @return array An array of sfValidator instances
   */
  public function getValidators()
  {
    return $this->validators;
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    $clean = $value;
    $errors = array();
    foreach ($this->validators as $validator)
    {
      try
      {
        $clean = $validator->clean($clean);
      }
      catch (sfValidatorError $e)
      {
        $errors[] = $e;
      }
    }

    if (count($errors))
    {
      if ($this->getMessage('invalid'))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      throw new sfValidatorErrorSchema($this, $errors);
    }

    return $clean;
  }

  /**
   * @see sfValidator
   */
  public function asString($indent = 0)
  {
    $validators = '';
    for ($i = 0, $max = count($this->validators); $i < $max; $i++)
    {
      $validators .= "\n".$this->validators[$i]->asString($indent + 2)."\n";

      if ($i < $max - 1)
      {
        $validators .= str_repeat(' ', $indent + 2).'and';
      }

      if ($i == $max - 2)
      {
        $options = $this->getOptionsWithoutDefaults();
        $messages = $this->getMessagesWithoutDefaults();

        if ($options || $messages)
        {
          $validators .= sprintf('(%s%s)',
            $options ? sfYamlInline::dump($options) : ($messages ? '{}' : ''),
            $messages ? ', '.sfYamlInline::dump($messages) : ''
          );
        }
      }
    }

    return sprintf("%s(%s%s)", str_repeat(' ', $indent), $validators, str_repeat(' ', $indent));
  }
}
