<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat                 = '',
    $errorRowFormat            = '',
    $errorListFormatInARow     = "  <ul class=\"error_list\">\n%errors%  </ul>\n",
    $errorRowFormatInARow      = "    <li>%error%</li>\n",
    $namedErrorRowFormatInARow = "    <li>%name%: %error%</li>\n",
    $decoratorFormat           = '';

  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
  {
    return strtr($this->getRowFormat(), array(
      '%label%'         => $label,
      '%field%'         => $field,
      '%error%'         => $this->formatErrorsForRow($errors),
      '%help%'          => $help,
      '%hidden_fields%' => is_null($hiddenFields) ? '%hidden_fields%' : $hiddenFields,
    ));
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
        $err = is_object($error) ? $error->__toString() : $error;

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
}
