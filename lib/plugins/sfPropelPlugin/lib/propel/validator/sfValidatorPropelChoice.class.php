<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPropelChoice validates that the value is one of the rows of a table.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorPropelChoice extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:      The model class (required)
   *  * criteria:   A criteria to use when retrieving objects
   *  * column:     The column name (null by default which means we use the primary key)
   *                must be in field name format
   *  * connection: The Propel connection to use (null by default)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('criteria', null);
    $this->addOption('column', null);
    $this->addOption('connection', null);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $criteria = is_null($this->getOption('criteria')) ? new Criteria() : $this->getOption('criteria');
    $criteria->add($this->getColumn(), $value);

    $object = call_user_func(array($this->getOption('model').'Peer', 'doSelectOne'), $criteria, $this->getOption('connection'));

    if (is_null($object))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $value;
  }

  /**
   * Returns the column to use for comparison.
   *
   * The primary key is used by default.
   *
   * @return string The column name
   */
  protected function getColumn()
  {
    if ($this->getOption('column'))
    {
      $columnName = $this->getOption('column');
    }
    else
    {
      $map = call_user_func(array($this->getOption('model').'Peer', 'getTableMap'));
      foreach ($map->getColumns() as $column)
      {
        if ($column->isPrimaryKey())
        {
          $columnName = strtolower($column->getColumnName());
          break;
        }
      }
    }

    return call_user_func(array($this->getOption('model').'Peer', 'translateFieldName'), $columnName, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);
  }
}
