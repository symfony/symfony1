<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatter allows to format a form schema with HTML formats.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfWidgetFormSchemaFormatter
{
  protected static 
    $translationCallable       = null;
  
  protected
    $rowFormat                 = '',
    $helpFormat                = '%help%',
    $errorRowFormat            = '',
    $errorListFormatInARow     = "  <ul class=\"error_list\">\n%errors%  </ul>\n",
    $errorRowFormatInARow      = "    <li>%error%</li>\n",
    $namedErrorRowFormatInARow = "    <li>%name%: %error%</li>\n",
    $decoratorFormat           = '',
    $widgetSchema              = null;

  /**
   * Constructor
   *
   * @param sfWidgetFormSchema $widgetSchema
   */
  public function __construct(sfWidgetFormSchema $widgetSchema)
  {
    $this->widgetSchema = $widgetSchema;
  }
    
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
  {
    return strtr($this->getRowFormat(), array(
      '%label%'         => $label,
      '%field%'         => $field,
      '%error%'         => $this->formatErrorsForRow($errors),
      '%help%'          => $this->formatHelp($help),
      '%hidden_fields%' => is_null($hiddenFields) ? '%hidden_fields%' : $hiddenFields,
    ));
  }
  
  /**
   * Translates a string using an i18n callable, if it has been provided
   *
   * @param  mixed  $subject     The subject to translate
   * @param  array  $parameters  Additional parameters to pass back to the callable
   * 
   * @return string
   */
  static public function translate($subject, $parameters = array())
  {
    if (false === $subject)
    {
      return false;  
    }
    
    if (is_null(self::$translationCallable))
    {
      return strtr($subject, $parameters);
    }
    
    if (self::$translationCallable instanceof sfCallable)
    {
      return self::$translationCallable->call($subject, $parameters);
    }

    return call_user_func(self::$translationCallable, $subject, $parameters);
  }
  
  /**
   * Returns the current i18n callable
   *
   * @return mixed
   */
  static public function getTranslationCallable()
  {
    return self::$translationCallable;
  }
  
  /**
   * Sets a callable which aims to translate form labels, errors and help messages 
   *
   * @param  mixed  $callable
   * 
   * @throws InvalidArgumentException if an invalid php callable or sfCallable has been provided
   */
  static public function setTranslationCallable($callable)
  {
    if (!$callable instanceof sfCallable && !is_callable($callable))
    {
      throw new InvalidArgumentException('Provided i18n callable should be either an instance of sfCallable or a valid PHP callable');
    }
    
    self::$translationCallable = $callable;
  }
  
  public function formatHelp($help)
  {
    if (!$help)
    {
      return '';
    }

    return strtr($this->getHelpFormat(), array('%help%' => self::translate($help)));
  }

  public function formatErrorRow($errors)
  {
    if (is_null($errors) || !$errors)
    {
      return '';
    }

    return strtr($this->getErrorRowFormat(), array('%errors%' => $this->formatErrorsForRow($errors)));
  }

  public function formatErrorsForRow($errors)
  {
    if (is_null($errors) || !$errors)
    {
      return '';
    }

    if (!is_array($errors))
    {
      $errors = array($errors);
    }

    return strtr($this->getErrorListFormatInARow(), array('%errors%' => implode('', $this->unnestErrors($errors))));
  }
  
  /**
   * Generates a label for the given field name.
   *
   * @param  string The field name
   *
   * @return string The label tag
   */
  public function generateLabel($name)
  {
    $labelName = $this->generateLabelName($name);

    if (false === $labelName)
    {
      return '';
    }

    $widgetId = $this->widgetSchema->generateId($this->widgetSchema->generateName($name));
    return $this->widgetSchema->renderContentTag('label', $labelName, array('for' => $widgetId));
  }

  /**
   * Generates the label name for the given field name.
   *
   * @param  string The field name
   *
   * @return string The label name
   */
  public function generateLabelName($name)
  {
    $label = $this->widgetSchema->getLabel($name);
    
    if (!$label && false !== $label)
    {
      $label = str_replace('_', ' ', ucfirst($name));
    }

    return self::translate($label);
  }

  protected function unnestErrors($errors, $prefix = '')
  {
    $newErrors = array();

    foreach ($errors as $name => $error)
    {
      if ($error instanceof ArrayAccess || is_array($error))
      {
        $newErrors = array_merge($newErrors, $this->unnestErrors($error, ($prefix ? $prefix.' > ' : '').$name));
      }
      else
      {
        if ($error instanceof sfValidatorError)
        {
          $err = self::translate($error->getMessageFormat(), $error->getArguments());
        }
        else
        {
          $err = self::translate($error);
        }

        if (!is_integer($name))
        {
          $newErrors[] = strtr($this->getNamedErrorRowFormatInARow(), array('%error%' => $err, '%name%' => ($prefix ? $prefix.' > ' : '').$name));
        }
        else
        {
          $newErrors[] = strtr($this->getErrorRowFormatInARow(), array('%error%' => $err));
        }
      }
    }

    return $newErrors;
  }
  
  public function setRowFormat($format)
  {
    $this->rowFormat = $format;
  }

  public function getRowFormat()
  {
    return $this->rowFormat;
  }

  public function setErrorRowFormat($format)
  {
    $this->errorRowFormat = $format;
  }

  public function getErrorRowFormat()
  {
    return $this->errorRowFormat;
  }

  public function setErrorListFormatInARow($format)
  {
    $this->errorListFormatInARow = $format;
  }

  public function getErrorListFormatInARow()
  {
    return $this->errorListFormatInARow;
  }

  public function setErrorRowFormatInARow($format)
  {
    $this->errorRowFormatInARow = $format;
  }

  public function getErrorRowFormatInARow()
  {
    return $this->errorRowFormatInARow;
  }

  public function setNamedErrorRowFormatInARow($format)
  {
    $this->namedErrorRowFormatInARow = $format;
  }

  public function getNamedErrorRowFormatInARow()
  {
    return $this->namedErrorRowFormatInARow;
  }

  public function setDecoratorFormat($format)
  {
    $this->decoratorFormat = $format;
  }

  public function getDecoratorFormat()
  {
    return $this->decoratorFormat;
  }

  public function setHelpFormat($format)
  {
    $this->helpFormat = $format;
  }

  public function getHelpFormat()
  {
    return $this->helpFormat;
  }
}
