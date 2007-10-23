<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSchemaFilter executes non schema validator on a schema input value.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorSchemaFilter extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param string      The field name
   * @param sfValidator The validator
   * @param array       An array of options
   * @param array       An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($field, sfValidator $validator, $options = array(), $messages = array())
  {
    $options['field']     = $field;
    $options['validator'] = $validator;

    parent::__construct(null, $options, $messages);
  }

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

    $value = isset($values[$this->getOption('field')]) ? $values[$this->getOption('field')] : null;

    $values[$this->getOption('field')] = $this->getOption('validator')->clean($value);

    return $values;
  }
}
