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
 * @version    SVN: $Id$
 */
class sfPropelData extends sfData
{
  protected
    $maps = array();

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
    $con = Propel::getConnection($connectionName);
    try
    {
      $con->begin();

      $this->doDeleteCurrentData($fixture_files);

      $this->doLoadData($fixture_files);

      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();
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
        return;
      }

      foreach ($datas as $key => $data)
      {
        // create a new entry in the database
        $obj = new $class();

        if (!is_array($data))
        {
          throw new Exception('You must give a name for each fixture data entry');
        }

        foreach ($data as $name => $value)
        {
          // foreign key?
          try
          {
            $column = $tableMap->getColumn($name);
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
          catch (PropelException $e)
          {
          }

          $pos = array_search($name, $column_names);
          $method = 'set'.sfInflector::camelize($name);
          if ($pos)
          {
            $obj->setByPosition($pos, $value);
          }
          else if (is_callable(array($obj, $method)))
          {
            $obj->$method($value);
          }
          else
          {
            throw new sfException(sprintf('Column "%s" does not exist for class "%s"', $name, $class));
          }
        }
        $obj->save();

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
      krsort($classes);
      foreach ($classes as $class)
      {
        $peer_class = trim($class.'Peer');

        if (!$classPath = sfAutoload::getClassPath($peer_class))
        {
          throw new sfException(sprintf('Unable to find path for class "%s".', $peer_class));
        }

        require_once($classPath);

        call_user_func(array($peer_class, 'doDeleteAll'));
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
      if (!$classPath = sfAutoload::getClassPath($mapBuilderClass))
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

    $con = Propel::getConnection($connectionName);

    // get tables
    if ('all' === $tables || is_null($tables))
    {
      // load all map builder classes
      $files = sfFinder::type('file')->name('*MapBuilder.php')->in(sfLoader::getModelDirs());
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

    // reordering tables to take foreign keys into account
    $move = true;
    while ($move)
    {
      foreach ($tables as $i => $tableName)
      {
        $tableMap = $this->maps[$tableName]->getDatabaseMap()->getTable(constant($tableName.'Peer::TABLE_NAME'));

        foreach ($tableMap->getColumns() as $column)
        {
          if ($column->isForeignKey())
          {
            $relatedTable = $this->maps[$tableName]->getDatabaseMap()->getTable($column->getRelatedTableName());
            if (array_search($relatedTable->getPhpName(), $tables) > $i)
            {
              unset($tables[$i]);
              $tables[] = $tableName;
              $move = true;
              continue 2;
            }
          }
        }

        $move = false;
      }
    }

    foreach ($tables as $tableName)
    {
      $tableMap = $this->maps[$tableName]->getDatabaseMap()->getTable(constant($tableName.'Peer::TABLE_NAME'));

      // get db info
      $rs = $con->executeQuery('SELECT * FROM '.constant($tableName.'Peer::TABLE_NAME'));

      while ($rs->next())
      {
        $pk = $tableName;
        $values = array();
        foreach ($tableMap->getColumns() as $column)
        {
          $col = strtolower($column->getColumnName());
          if ($column->isPrimaryKey())
          {
            $pk .= '_'.$rs->get($col);
          }
          else if ($column->isForeignKey())
          {
            $relatedTable = $this->maps[$tableName]->getDatabaseMap()->getTable($column->getRelatedTableName());

            $values[$col] = $relatedTable->getPhpName().'_'.$rs->get($col);
          }
          else
          {
            $values[$col] = $rs->get($col);
          }
        }

        if (!isset($dumpData[$tableName]))
        {
          $dumpData[$tableName] = array();
        }

        $dumpData[$tableName][$pk] = $values;
      }
    }

    // save to file(s)
    if ($sameFile)
    {
      $yaml = Spyc::YAMLDump($dumpData);
      file_put_contents($directory_or_file, $yaml);
    }
    else
    {
      foreach ($dumpData as $table => $data)
      {
        $yaml = Spyc::YAMLDump($data);
        file_put_contents($directory_or_file."/$table.yml", $yaml);
      }
    }
  }
}
