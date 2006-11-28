<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Propel CRUD generator.
 *
 * This class generates a basic CRUD module with propel.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

class sfPropelCrudGenerator extends sfAdminGenerator
{
  public function initialize($generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelCrud');
  }

  protected function loadPrimaryKeys()
  {
    foreach ($this->tableMap->getColumns() as $column)
    {
      if ($column->isPrimaryKey())
      {
        $this->primaryKey[] = $column;
      }
    }

    if (!count($this->primaryKey))
    {
      throw new sfException(sprintf('Cannot generate a module for a model without a primary key (%s)', $this->className));
    }
  }

  protected function loadMapBuilderClasses()
  {
    // we must load all map builder classes to be able to deal with foreign keys (cf. editSuccess.php template)
    $classes = sfFinder::type('file')->name('*MapBuilder.php')->in(sfLoader::getModelDirs());
    foreach ($classes as $class)
    {
      $class_map_builder = basename($class, '.php');
      $maps[$class_map_builder] = new $class_map_builder();
      if (!$maps[$class_map_builder]->isBuilt())
      {
        $maps[$class_map_builder]->doBuild();
      }

      if ($this->className == str_replace('MapBuilder', '', $class_map_builder))
      {
        $this->map = $maps[$class_map_builder];
      }
    }
    if (!$this->map)
    {
      throw new sfException('The model class "'.$this->className.'" does not exist.');
    }

    $this->tableMap = $this->map->getDatabaseMap()->getTable(constant($this->className.'Peer::TABLE_NAME'));
  }

  // generates a PHP call to an object helper
  function getPHPObjectHelper($helperName, $column, $params)
  {
    return sprintf ('object_%s($%s, \'%s\', %s)', $helperName, $this->getSingularName(), $this->getColumnGetter($column, false), $params);
  }

  // returns the getter either non-developped: 'getFoo'
  // or developped: '$class->getFoo()'
  function getColumnGetter($column, $developed = false , $prefix = '')
  {
    $getter = 'get'.$column->getPhpName();
    if ($developed)
    {
      $getter = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $getter);
    }

    return $getter;
  }

  // used for foreign keys only; this method should be removed when we use
  // sfAdminColumn instead
  function getRelatedClassName($column)
  {
    $relatedTable = $this->getMap()->getDatabaseMap()->getTable($column->getRelatedTableName());

    return $relatedTable->getPhpName();
  }
}
