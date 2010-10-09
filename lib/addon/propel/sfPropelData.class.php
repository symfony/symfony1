<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class is the Propel implementation of sfData.  It interacts with the data source
 * and loads data.
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPropelData.class.php 12958 2008-11-12 18:03:09Z hartym $
 */
class sfPropelData extends sfData
{
  protected
    $maps           = array(),
    $deletedClasses = array(),
    $con            = null;

  // symfony load-data (file|dir)
  /**
   * Loads data from a file or directory into a Propel data source
   *
   * @param mixed A file or directory path
   * @param string The Propel connection name, default 'propel'
   *
   * @throws Exception If the database throws an error, rollback transaction and rethrows exception
   */
  public function loadData($directory_or_file = null, $connectionName = 'propel')
  {
    $fixture_files = $this->getFiles($directory_or_file);

    // wrap all database operations in a single transaction
    $this->con = Propel::getConnection($connectionName);
    try
    {
      $this->con->begin();

      $this->doDeleteCurrentData($fixture_files);

      $this->doLoadData($fixture_files);

      $this->con->commit();
    }
    catch (Exception $e)
    {
      $this->con->rollback();
      throw $e;
    }
  }

  /**
   * Implements the abstract loadDataFromArray method and loads the data using the generated data model.
   *
   * @param array The data to be loaded into the data source
   *
   * @throws Exception If data is unnamed.
   * @throws sfException If an object defined in the model does not exist in the data
   * @throws sfException If a column that does not exist is referenced
   */
  public function loadDataFromArray($data)
  {
    if ($data === null)
    {
      // no data
      return;
    }

    foreach ($data as $class => $datas)
    {
      $class = trim($class);

      $peer_class = $class.'Peer';

      // load map class
      $this->loadMapBuilder($class);

      $tableMap = $this->maps[$class]->getDatabaseMap()->getTable(constant($peer_class.'::TABLE_NAME'));

      $column_names = call_user_func_array(array($peer_class, 'getFieldNames'), array(BasePeer::TYPE_FIELDNAME));

      // iterate through datas for this class
      // might have been empty just for force a table to be emptied on import
      if (!is_array($datas))
      {
        continue;
      }

      foreach ($datas as $key => $data)
      {
        // create a new entry in the database
        $obj = new $class();

        if (!$obj instanceof BaseObject)
        {
          throw new Exception(sprintf('The class "%s" is not a Propel class. This probably means there is already a class named "%s" somewhere in symfony or in your project.', $class, $class));
        }

        if (!is_array($data))
        {
          throw new Exception(sprintf('You must give a name for each fixture data entry (class %s)', $class));
        }

        foreach ($data as $name => $value)
        {
          $isARealColumn = true;
          try
          {
            $column = $tableMap->getColumn($name);
          }
          catch (PropelException $e)
          {
            $isARealColumn = false;
          }

          // foreign key?
          if ($isARealColumn)
          {
            if ($column->isForeignKey() && !is_null($value))
            {
              $relatedTable = $this->maps[$class]->getDatabaseMap()->getTable($column->getRelatedTableName());

              if (!isset($this->object_references[$relatedTable->getPhpName().'_'.$value]))
              {
                throw new sfException(sprintf('The object "%s" from class "%s" is not defined in your data file.', $value, $relatedTable->getPhpName()));
              }

              $value = $this->object_references[$relatedTable->getPhpName().'_'.$value];
            }
          }

          if (false !== $pos = array_search($name, $column_names))
          {
            $obj->setByPosition($pos, $value);
          }
          else if (is_callable(array($obj, $method = 'set'.sfInflector::camelize($name))))
          {
            $obj->$method($value);
          }
          else
          {
            $error = 'Column "%s" does not exist for class "%s"';
            $error = sprintf($error, $name, $class);
            throw new sfException($error);
          }
        }
        $obj->save($this->con);

        // save the id for future reference
        if (method_exists($obj, 'getPrimaryKey'))
        {
          $this->object_references[$class.'_'.$key] = $obj->getPrimaryKey();
        }
      }
    }
  }

  /**
   * Clears existing data from the data source by reading the fixture files
   * and deleting the existing data for only those classes that are mentioned
   * in the fixtures.
   *
   * @param array The list of YAML files.
   *
   * @throws sfException If a class mentioned in a fixture can not be found
   */
  protected function doDeleteCurrentData($fixture_files)
  {
    // delete all current datas in database
    if (!$this->deleteCurrentData)
    {
      return;
    }

    rsort($fixture_files);
    foreach ($fixture_files as $fixture_file)
    {
      $data = sfYaml::load($fixture_file);

      if ($data === null)
      {
        // no data
        continue;
      }

      $classes = array_keys($data);
      foreach (array_reverse($classes) as $class)
      {
        $class = trim($class);
        if (in_array($class, $this->deletedClasses))
        {
          continue;
        }

        $peer_class = $class.'Peer';

        if (!$classPath = sfCore::getClassPath($peer_class))
        {
          throw new sfException(sprintf('Unable to find path for class "%s".', $peer_class));
        }

        require_once($classPath);

        call_user_func(array($peer_class, 'doDeleteAll'), $this->con);

        $this->deletedClasses[] = $class;
      }
    }
  }

  /**
   * Loads the mappings for the classes
   *
   * @param string The model class name
   *
   * @throws sfException If the class cannot be found
   */
  protected function loadMapBuilder($class)
  {
    $mapBuilderClass = $class.'MapBuilder';
    if (!isset($this->maps[$class]))
    {
      if (!$classPath = sfCore::getClassPath($mapBuilderClass))
      {
        throw new sfException(sprintf('Unable to find path for class "%s".', $mapBuilderClass));
      }

      require_once($classPath);
      $this->maps[$class] = new $mapBuilderClass();
      $this->maps[$class]->doBuild();
    }
  }

  /**
   * Dumps data to fixture from one or more tables.
   *
   * @param string directory or file to dump to
   * @param mixed  name or names of tables to dump (or all to dump all tables)
   * @param string connection name
   */
  public function dumpData($directory_or_file = null, $tables = 'all', $connectionName = 'propel')
  {
    $sameFile = true;
    if (is_dir($directory_or_file))
    {
      // multi files
      $sameFile = false;
    }
    else
    {
      // same file
      // delete file
    }

    $this->con = Propel::getConnection($connectionName);

    // get tables
    if ('all' === $tables || is_null($tables))
    {
      // load all map builder classes
      $files = sfFinder::type('file')->ignore_version_control()->name('*MapBuilder.php')->in(sfLoader::getModelDirs());
      foreach ($files as $file)
      {
        $mapBuilderClass = basename($file, '.php');
        $map = new $mapBuilderClass();
        $map->doBuild();
      }

      $dbMap = Propel::getDatabaseMap($connectionName);
      $tables = array();
      foreach ($dbMap->getTables() as $table)
      {
        $tables[] = $table->getPhpName();
      }
    }
    else if (!is_array($tables))
    {
      $tables = array($tables);
    }

    $dumpData = array();

    // load map classes
    array_walk($tables, array($this, 'loadMapBuilder'));

    $tables = $this->fixOrderingOfForeignKeyData($tables);
    foreach ($tables as $tableName)
    {
      $tableMap = $this->maps[$tableName]->getDatabaseMap()->getTable(constant($tableName.'Peer::TABLE_NAME'));
      $hasParent = false;
      $haveParents = false;
      $fixColumn = null;
      foreach ($tableMap->getColumns() as $column)
      {
        $col = strtolower($column->getColumnName());
        if ($column->isForeignKey())
        {
          $relatedTable = $this->maps[$tableName]->getDatabaseMap()->getTable($column->getRelatedTableName());
          if ($tableName === $relatedTable->getPhpName())
          {
            if ($hasParent)
            {
              $haveParents = true;
            }
            else
            {
              $fixColumn = $column;
              $hasParent = true;
            }
          }
        }
      }

      if ($haveParents)
      {
        // unable to dump tables having multi-recursive references
        continue;
      }

      // get db info
      $resultsSets = array();
      if ($hasParent)
      {
        $resultsSets = $this->fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $fixColumn);
      }
      else
      {
        $resultsSets[] = $this->con->executeQuery('SELECT * FROM '.constant($tableName.'Peer::TABLE_NAME'));
      }

      foreach ($resultsSets as $rs)
      {
        if($rs->getRecordCount() > 0 && !isset($dumpData[$tableName])){
          $dumpData[$tableName] = array();
        }

        while ($rs->next())
        {
          $pk = $tableName;
          $values = array();
          $primaryKeys = array();
          $foreignKeys = array();

          foreach ($tableMap->getColumns() as $column)
          {
            $col = strtolower($column->getColumnName());
            $isPrimaryKey = $column->isPrimaryKey();

            if (is_null($rs->get($col)))
            {
              continue;
            }

            if ($isPrimaryKey)
            {
              $value = $rs->get($col);
              $pk .= '_'.$value;
              $primaryKeys[$col] = $value;
            }

            if ($column->isForeignKey())
            {
              $relatedTable = $this->maps[$tableName]->getDatabaseMap()->getTable($column->getRelatedTableName());
              if ($isPrimaryKey)
              {
                $foreignKeys[$col] = $rs->get($col);
                $primaryKeys[$col] = $relatedTable->getPhpName().'_'.$rs->get($col);
              }
              else
              {
                $values[$col] = $relatedTable->getPhpName().'_'.$rs->get($col);
              }
            }
            elseif (!$isPrimaryKey || ($isPrimaryKey && !$tableMap->isUseIdGenerator()))
            {
              // We did not want auto incremented primary keys
              $values[$col] = $rs->get($col);
            }
          }

          if (count($primaryKeys) > 1 || (count($primaryKeys) > 0 && count($foreignKeys) > 0))
          {
            $values = array_merge($primaryKeys, $values);
          }

          $dumpData[$tableName][$pk] = $values;
        }
      }
    }

    // save to file(s)
    if ($sameFile)
    {
      file_put_contents($directory_or_file, Spyc::YAMLDump($dumpData));
    }
    else
    {
      $i = 0;
      foreach ($tables as $tableName)
      {
        if (!isset($dumpData[$tableName]))
        {
          continue;
        }

        file_put_contents(sprintf("%s/%03d-%s.yml", $directory_or_file, ++$i, $tableName), Spyc::YAMLDump(array($tableName => $dumpData[$tableName])));
      }
    }
  }

  /**
   * Fixes the ordering of foreign key data, by outputting data a foreign key depends on before the table with the foreign key.
   *
   * @param array The array with the class names.
   */
  public function fixOrderingOfForeignKeyData($classes)
  {
    // reordering classes to take foreign keys into account
    for ($i = 0, $count = count($classes); $i < $count; $i++)
    {
      $class = $classes[$i];
      $tableMap = $this->maps[$class]->getDatabaseMap()->getTable(constant($class.'Peer::TABLE_NAME'));
      foreach ($tableMap->getColumns() as $column)
      {
        if ($column->isForeignKey())
        {
          $relatedTable = $this->maps[$class]->getDatabaseMap()->getTable($column->getRelatedTableName());
          $relatedTablePos = array_search($relatedTable->getPhpName(), $classes);

          // check if relatedTable is after the current table
          if ($relatedTablePos > $i)
          {
            // move related table 1 position before current table
            $classes = array_merge(
              array_slice($classes, 0, $i),
              array($classes[$relatedTablePos]),
              array_slice($classes, $i, $relatedTablePos - $i),
              array_slice($classes, $relatedTablePos + 1)
            );

            // we have moved a table, so let's see if we are done
            return $this->fixOrderingOfForeignKeyData($classes);
          }
        }
      }
    }

    return $classes;
  }

  protected function fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $column, $in = null)
  {
    $rs = $this->con->executeQuery(sprintf('SELECT * FROM %s WHERE %s %s',
      constant($tableName.'Peer::TABLE_NAME'),
      strtolower($column->getColumnName()),
      is_null($in) ? 'IS NULL' : 'IN ('.$in.')'
    ));
    $in = array();
    while ($rs->next())
    {
      $in[] = "'".$rs->get(strtolower($column->getRelatedColumnName()))."'";
    }

    if ($in = implode(', ', $in))
    {
      $rs->seek(0);
      $resultsSets[] = $rs;
      $resultsSets = $this->fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $column, $in);
    }

    return $resultsSets;
  }
}
