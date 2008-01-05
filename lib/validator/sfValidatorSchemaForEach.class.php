<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSchemaForEach wraps a validator multiple times in a single validator.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorSchemaForEach extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param sfValidator Initial validators
   * @param integer     The number of times to replicate the validator
   * @param array       An array of options
   * @param array       An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($validator, $count, $options = array(), $messages = array())
  {
    $fields = array();
    for ($i = 0; $i < $count; $i++)
    {
      $clone = clone $validator;

      $fields[$i] = $clone;
    }

    parent::__construct($fields, $options, $messages);
  }

  /**
   * @see sfValidator
   */
  public function asString($indent = 0)
  {
    throw new Exception('Unable to convert a sfValidatorSchemaForEach to string.');
  }
}
