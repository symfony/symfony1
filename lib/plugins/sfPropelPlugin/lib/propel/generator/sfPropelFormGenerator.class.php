<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Propel form generator.
 *
 * This class generates a Propel forms.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelFormGenerator extends sfGenerator
{
  protected
    $dbMap = null;

  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager A sfGeneratorManager instance
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelForm');
  }

  /**
   * Generates classes and templates in cache.
   *
   * @param array The parameters
   *
   * @return string The data to put in configuration cache
   */
  public function generate($params = array())
  {
    $this->params = $params;

    if (!isset($this->params['connection']))
    {
      throw new sfParseException('You must specify a "connection" parameter.');
    }

    $this->loadBuilders();

    $this->dbMap = Propel::getDatabaseMap($this->params['connection']);

    // create the project base class for all forms
    $file = sfConfig::get('sf_lib_dir').'/form/BaseFormPropel.class.php';
    if (!file_exists($file))
    {
      if (!is_dir(sfConfig::get('sf_lib_dir').'/form'))
      {
        mkdir(sfConfig::get('sf_lib_dir').'/form', 0777, true);
      }

      file_put_contents($file, $this->evalTemplate('sfPropelFormBaseTemplate.php'));
    }

    // create a form class for every Propel class
    foreach ($this->dbMap->getTables() as $tableName => $table)
    {
      $this->table = $table;

      // find the package to store forms in the same directory as the model classes
      $packages = explode('.', constant($table->getPhpName().'Peer::CLASS_DEFAULT'));
      $baseDir = sfConfig::get('sf_root_dir').'/'.implode(DIRECTORY_SEPARATOR, array_slice($packages, 0, count($packages) - 2)).'/form';

      if (!is_dir($baseDir.'/base'))
      {
        mkdir($baseDir.'/base', 0777, true);
      }

      file_put_contents($baseDir.'/base/Base'.$table->getPhpName().'Form.class.php', $this->evalTemplate('sfPropelFormGeneratedTemplate.php'));
      if (!file_exists($classFile = $baseDir.'/'.$table->getPhpName().'Form.class.php'))
      {
        file_put_contents($classFile, $this->evalTemplate('sfPropelFormTemplate.php'));
      }
    }
  }

  /**
   * Returns an array of tables that represents a many to many relationship.
   *
   * A table is considered to be a m2m table if it has 2 foreign keys that are also primary keys.
   *
   * @return array An array of tables.
   */
  public function getManyToManyTables()
  {
    $tables = array();

    // go through all tables to find m2m relationships
    foreach ($this->dbMap->getTables() as $tableName => $table)
    {
      foreach ($table->getColumns() as $column)
      {
        if ($column->isForeignKey() && $column->isPrimaryKey() && $this->table->getPhpName() == $this->getForeignTable($column)->getPhpName())
        {
          // we have a m2m relationship
          // find the other primary key
          foreach ($table->getColumns() as $relatedColumn)
          {
            if ($relatedColumn->isForeignKey() && $relatedColumn->isPrimaryKey() && $this->table->getPhpName() != $this->getForeignTable($relatedColumn)->getPhpName())
            {
              // we have the related table
              $tables[] = array(
                'middleTable'   => $table,
                'relatedTable'  => $this->getForeignTable($relatedColumn),
                'column'        => $column,
                'relatedColumn' => $relatedColumn,
              );

              break 2;
            }
          }
        }
      }
    }

    return $tables;
  }

  /**
   * Returns PHP names for all foreign keys of the current table.
   *
   * This method does not returns foreign keys that are also primary keys.
   *
   * @return array An array of foreign key PHP names
   */
  public function getForeignKeyNames()
  {
    $names = array();
    foreach ($this->table->getColumns() as $column)
    {
      if (!$column->isPrimaryKey() && $column->isForeignKey())
      {
        $names[] = $this->getForeignTable($column)->getPhpName();
      }
    }

    foreach ($this->getManyToManyTables() as $tables)
    {
      $names[] = $tables['relatedTable']->getPhpName();
    }

    return $names;
  }

  /**
   * Returns the first primary key column of the current table.
   *
   * @param ColumnMap A ColumnMap object
   */
  public function getPrimaryKey()
  {
    foreach ($this->table->getColumns() as $column)
    {
      if ($column->isPrimaryKey())
      {
        return $column;
      }
    }
  }

  /**
   * Returns the foreign table associated with a column.
   *
   * @param  ColumnMap A ColumnMap object
   *
   * @return TableMap  A TableMap object
   */
  public function getForeignTable(ColumnMap $column)
  {
    return $this->dbMap->getTable($column->getRelatedTableName());
  }

  /**
   * Returns a sfWidgetForm class name for a given column.
   *
   * @param  ColumnMap A ColumnMap object
   *
   * @return string    The name of a subclass of sfWidgetForm
   */
  public function getWidgetClassForColumn(ColumnMap $column)
  {
    switch ($column->getCreoleType())
    {
      case CreoleTypes::BOOLEAN:
        $name = 'InputCheckbox';
        break;
      case CreoleTypes::LONGVARCHAR:
        $name = 'Textarea';
        break;
      case CreoleTypes::DATE:
        $name = 'Date';
        break;
      case CreoleTypes::TIME:
        $name = 'Time';
        break;
      case CreoleTypes::TIMESTAMP:
        $name = 'DateTime';
        break;
      default:
        $name = 'Input';
    }

    if ($column->isPrimaryKey())
    {
      $name = 'InputHidden';
    }
    else if ($column->isForeignKey())
    {
      $name = 'Select';
    }

    return sprintf('sfWidgetForm%s', $name);
  }

  /**
   * Returns a PHP string representing options to pass to a widget for a given column.
   *
   * @param  ColumnMap A ColumnMap object
   *
   * @return string    The options to pass to the widget as a PHP string
   */
  public function getWidgetOptionsForColumn(ColumnMap $column)
  {
    $options = array();

    if (!$column->isPrimaryKey() && $column->isForeignKey())
    {
      $options[] = sprintf('\'choices\' => new sfCallable(array($this, \'get%sChoices\'))', $this->getForeignTable($column)->getPhpName());
    }

    return count($options) ? sprintf('array(%s)', implode(', ', $options)) : '';
  }

  /**
   * Returns a sfValidator class name for a given column.
   *
   * @param  ColumnMap A ColumnMap object
   *
   * @return string    The name of a subclass of sfValidator
   */
  public function getValidatorClassForColumn(ColumnMap $column)
  {
    switch ($column->getCreoleType())
    {
      case CreoleTypes::BOOLEAN:
        $name = 'Boolean';
        break;
      case CreoleTypes::CHAR:
      case CreoleTypes::VARCHAR:
      case CreoleTypes::LONGVARCHAR:
        $name = 'String';
        break;
      case CreoleTypes::DOUBLE:
      case CreoleTypes::FLOAT:
      case CreoleTypes::NUMERIC:
      case CreoleTypes::DECIMAL:
      case CreoleTypes::REAL:
        $name = 'Number';
        break;
      case CreoleTypes::INTEGER:
      case CreoleTypes::SMALLINT:
      case CreoleTypes::TINYINT:
      case CreoleTypes::BIGINT:
      case CreoleTypes::YEAR:
        $name = 'Integer';
        break;
      case CreoleTypes::DATE:
        $name = 'Date';
        break;
      case CreoleTypes::TIME:
        $name = 'Time';
        break;
      case CreoleTypes::TIMESTAMP:
        $name = 'DateTime';
        break;
      default:
        $name = 'Pass';
    }

    if (!$column->isPrimaryKey() && $column->isForeignKey())
    {
      $name = 'Choice';
    }

    return sprintf('sfValidator%s', $name);
  }

  /**
   * Returns a PHP string representing options to pass to a validator for a given column.
   *
   * @param  ColumnMap A ColumnMap object
   *
   * @return string    The options to pass to the validator as a PHP string
   */
  public function getValidatorOptionsForColumn(ColumnMap $column)
  {
    $options = array();

    switch ($column->getType())
    {
      case CreoleTypes::CHAR:
      case CreoleTypes::VARCHAR:
      case CreoleTypes::LONGVARCHAR:
        if ($column->getSize())
        {
          $options[] = sprintf('\'max_length\' => %s', $column->getSize());
        }
        break;
      default:
    }

    if (!$column->isPrimaryKey() && $column->isForeignKey())
    {
      $options[] = sprintf('\'choices\' => new sfCallable(array($this, \'get%sIdentifierChoices\'))', $this->dbMap->getTable($column->getRelatedTableName())->getPhpName());
    }

    if (!$column->isNotNull() || $column->isPrimaryKey())
    {
      $options[] = '\'required\' => false';
    }

    return count($options) ? sprintf('array(%s)', implode(', ', $options)) : '';
  }

  /**
   * Returns the maximum length for a column name.
   *
   * @return integer The length of the longer column name
   */
  public function getColumnNameMaxLength()
  {
    $max = 0;
    foreach ($this->table->getColumns() as $column)
    {
      if (($m = strlen($column->getColumnName())) > $max)
      {
        $max = $m;
      }
    }

    foreach ($this->getManyToManyTables() as $tables)
    {
      if (($m = strlen($tables['relatedTable']->getName().'_list')) > $max)
      {
        $max = $m;
      }
    }

    return $max;
  }

  /**
   * Returns an array of primary key column names.
   *
   * @return array An array of primary key column names
   */
  public function getPrimaryKeyColumNames()
  {
    $pks = array();
    foreach ($this->table->getColumns() as $column)
    {
      if ($column->isPrimaryKey())
      {
        $pks[] = strtolower($column->getColumnName());
      }
    }

    return $pks;
  }

  /**
   * Returns a PHP string representation for the array of all primary key column names.
   *
   * @return string A PHP string representation for the array of all primary key column names
   *
   * @see getPrimaryKeyColumNames()
   */
  public function getPrimaryKeyColumNamesAsString()
  {
    return sprintf('array(\'%s\')', implode('\', \'', $this->getPrimaryKeyColumNames()));
  }

  /**
   * Returns true if the current table is internationalized.
   *
   * @return Boolean true if the current table is internationalized, false otherwise
   */
  public function isI18n()
  {
    return method_exists($this->table->getPhpName().'Peer', 'getI18nModel');
  }

  /**
   * Returns the i18n model name for the current table.
   *
   * @return string The model class name
   */
  public function getI18nModel()
  {
    return call_user_func(array($this->table->getPhpName().'Peer', 'getI18nModel'));
  }

  /**
   * Loads all Propel builders.
   */
  protected function loadBuilders()
  {
    $classes = sfFinder::type('file')->name('*MapBuilder.php')->in(sfLoader::getModelDirs());
    foreach ($classes as $class)
    {
      $class = basename($class, '.php');
      $map = new $class();
      if (!$map->isBuilt())
      {
        $map->doBuild();
      }
    }
  }
}
