<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPropelChoiceMany validates than an array of values is in the array of the existing rows of a table.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorPropelChoiceMany.class.php 15627 2009-02-19 13:35:10Z Kris.Wallsmith $
 */
class sfValidatorPropelChoiceMany extends sfValidatorPropelChoice
{
  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    $criteria = is_null($this->getOption('criteria')) ? new Criteria() : clone $this->getOption('criteria');
    $criteria->addAnd($this->getColumn(), $values, Criteria::IN);

    $objects = call_user_func(array($this->getOption('model').'Peer', 'doSelect'), $criteria, $this->getOption('connection'));

    if (count($objects) != count($values))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $values));
    }

    return $values;
  }
}
