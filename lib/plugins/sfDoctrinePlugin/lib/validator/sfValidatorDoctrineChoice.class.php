<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDoctrineChoice validates that the value is one of the rows of a table.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfValidatorDoctrineChoice.class.php 8804 2008-05-06 12:11:10Z fabien $
 */
class sfValidatorDoctrineChoice extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:      The model class (required)
   *  * alias:      The alias of the root component used in the query
   *  * query:      A query to use when retrieving objects
   *  * column:     The column name (null by default which means we use the primary key)
   *                must be in field name format
   *  * connection: The Doctrine connection to use (null by default)
   *  * multiple:   true if the select tag must allow multiple selections
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('alias', 'a');
    $this->addOption('query', null);
    $this->addOption('column', null);
    $this->addOption('connection', null);
    $this->addOption('multiple', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if ($this->getOption('multiple'))
    {
      if (!is_array($value))
      {
        $value = array($value);
      }

      if (isset($value[0]) && !$value[0])
      {
        unset($value[0]);
      }

      $a = $this->getOption('alias');
      $q = null === $this->getOption('query') ? Doctrine::getTable($this->getOption('model'))->createQuery($a) : $this->getOption('query');
      $q = $q->andWhereIn($a . '.' . $this->getColumn(), $value);

      $objects = $q->execute();

      if (count($objects) != count($value))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }
    else
    {
      $a = ($q = $this->getOption('query')) ? $q->getRootAlias():$this->getOption('alias');
      $q = null === $this->getOption('query') ? Doctrine::getTable($this->getOption('model'))->createQuery($a) : $this->getOption('query');
      $q->addWhere($a.'.'.$this->getColumn().' = ?', $value);

      $object = $q->fetchOne();

      if (!$object)
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
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
    $table = Doctrine::getTable($this->getOption('model'));
    if ($this->getOption('column'))
    {
      $columnName = $this->getOption('column');
    }
    else
    {
      $identifier = (array) $table->getIdentifier();
      $columnName = current($identifier);
    }

    return $table->getColumnName($columnName);
  }
}
