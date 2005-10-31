<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNumberValidator verifies a parameter is a number and allows you to apply
 * size constraints.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>max</b>        - [none]                  - Maximum number size.
 * # <b>max_error</b>  - [Input is too large]    - An error message to use when
 *                                                 input is too large.
 * # <b>min</b>        - [none]                  - Minimum number size.
 * # <b>min_error</b>  - [Input is too small]    - An error message to use when
 *                                                 input is too small.
 * # <b>nan_error</b>  - [Input is not a number] - Default error message when
 *                                                 input is not a number.
 * # <b>type</b>       - [Any]                   - Type of number (Any, Float).
 * # <b>type_error</b> - [Input is not a number] - An error message to use when
 *                                                 input is not a number.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfNumberValidator extends sfValidator
{
  /**
   * Execute this validator.
   *
   * @param mixed A file or parameter value/array.
   * @param error An error message reference.
   *
   * @return bool true, if this validator executes successfully, otherwise false.
   */
  public function execute (&$value, &$error)
  {
    if (!is_numeric($value))
    {
      // it's NaN, what nerve!
      $error = $this->getParameterHolder()->get('nan_error');

      return false;
    }

    $type = strtolower($this->getParameterHolder()->get('type'));

    if ($type == 'float')
    {
      if (substr_count($value, '.') != 1)
      {
        // value isn't a float, shazbot!
        $error = $this->getParameterHolder()->get('type_error');

        return false;
      }

      // cast our value to a float
      $value = (float) $value;
    }

    $min = $this->getParameterHolder()->get('min');

    if ($min != null && $value < $min)
    {
      // too small
      $error = $this->getParameterHolder()->get('min_error');

      return false;
    }

    $max = $this->getParameterHolder()->get('max');

    if ($max != null && $value > $max)
    {
      // too large
      $error = $this->getParameterHolder()->get('max_error');

      return false;
    }

    return true;
  }

  /**
   * Initialize this validator.
   *
   * @param Context The current application context.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize ($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context);

    // set defaults
    $this->getParameterHolder()->set('max',        null);
    $this->getParameterHolder()->set('max_error',  'Input is too large');
    $this->getParameterHolder()->set('min',        null);
    $this->getParameterHolder()->set('min_error',  'Input is too small');
    $this->getParameterHolder()->set('nan_error',  'Input is not a number');
    $this->getParameterHolder()->set('type',       'Any');
    $this->getParameterHolder()->set('type_error', 'Input is not a number');

    $this->getParameterHolder()->add($parameters);

    // check user-specified parameters
    $type = strtolower($this->getParameterHolder()->get('type'));

    if ($type != 'any' && $type != 'float')
    {
      // unknown type
      $error = 'Unknown number type "%s" in NumberValidator';
      $error = sprintf($error, $this->getParameterHolder()->get('type'));

      throw new sfValidatorException($error);
    }

    return true;
  }
}

?>