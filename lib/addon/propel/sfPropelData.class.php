<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelData
{
  protected
    $deleteCurrentData = true,
    $maps              = array(),
    $object_references = array();

  public function setDeleteCurrentData($boolean)
  {
    $this->deleteCurrentData = $boolean;
  }

  public function getDeleteCurrentData()
  {
    return $this->deleteCurrentData;
  }

  // symfony load-data (file|dir)
  public function loadData($directory_or_file = null, $connectionName = 'propel')
  {
    $fixture_files = $this->getFiles($directory_or_file);

    // wrap all database operations in a single transaction
    $con = Propel::getConnection();
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

  protected function doLoadDataFromFile($fixture_file)
  {
    // import new datas
    $data = sfYaml::load($fixture_file);

    $this->loadDataFromArray($data);
  }

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
      if (is_array($datas))
      {
        foreach ($datas as $key => $data)
        {
          // create a new entry in the database
          $obj = new $class();
          foreach ($data as $name => $value)
          {
            // foreign key?
            try
            {
              $column = $tableMap->getColumn($name);
              if ($column->isForeignKey())
              {
                $relatedTable = $this->maps[$class]->getDatabaseMap()->getTable($column->getRelatedTableName());
                if (!isset($this->object_references[$relatedTable->getPhpName().'_'.$value]))
                {
                  $error = 'The object "%s" from class "%s" is not defined in your data file.';
                  $error = sprintf($error, $value, $relatedTable->getPhpName());
                  throw new sfException($error);
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
              $error = 'Column "%s" does not exist for class "%s"';
              $error = sprintf($error, $name, $class);
              throw new sfException($error);
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
  }

  protected function doLoadData($fixture_files)
  {
    $this->object_references = array();
    $this->maps = array();

    sort($fixture_files);
    foreach ($fixture_files as $fixture_file)
    {
      $this->doLoadDataFromFile($fixture_file);
    }
  }

  protected function doDeleteCurrentData($fixture_files)
  {
    // delete all current datas in database
    if ($this->deleteCurrentData)
    {
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

          if (!$classPath = sfCore::getClassPath($peer_class))
          {
            throw new sfException(sprintf('Unable to find path for class "%s".', $peer_class));
          }

          require_once($classPath);

          call_user_func(array($peer_class, 'doDeleteAll'));
        }
      }
    }
  }

  protected function getFiles($directory_or_file = null)
  {
    // directory or file?
    $fixture_files = array();
    if (!$directory_or_file)
    {
      $directory_or_file = sfConfig::get('sf_data_dir').'/fixtures';
    }

    if (is_file($directory_or_file))
    {
      $fixture_files[] = $directory_or_file;
    }
    else if (is_dir($directory_or_file))
    {
      $fixture_files = sfFinder::type('file')->name('*.yml')->in($directory_or_file);
    }
    else
    {
      throw new sfInitializationException('You must give a directory or a file.');
    }

    return $fixture_files;
  }

  protected function loadMapBuilder($class)
  {
    $class_map_builder = $class.'MapBuilder';
    if (!isset($this->maps[$class]))
    {
      if (!$classPath = sfCore::getClassPath($class_map_builder))
      {
        throw new sfException(sprintf('Unable to find path for class "%s".', $class_map_builder));
      }

      require_once($classPath);
      $this->maps[$class] = new $class_map_builder();
      $this->maps[$class]->doBuild();
    }
  }

  /**
   * Dumps data to fixture from 1 or more tables.
   *
   * @param string directory or file to dump to
   * @param mixed name or names of tables to dump
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

    $con = sfContext::getInstance()->getDatabaseConnection($connectionName);

    // get tables
    if ('all' === $tables || null === $tables)
    {
      $tables = sfFinder::type('file')->name('/(?<!Peer)\.php$/')->maxdepth(0)->in(sfConfig::get('sf_model_lib_dir'));
      foreach ($tables as &$table)
      {
        $table = basename($table, '.php');
      }
    }
    else if (!is_array($tables))
    {
      $tables = array($tables);
    }

    $dumpData = array();

    // load map classes
    array_walk($tables, array($this, 'loadMapBuilder'));

    foreach ($tables as $table)
    {
      $tableMap = $this->maps[$table]->getDatabaseMap()->getTable(constant($table.'Peer::TABLE_NAME'));

      // get db info
      $rs = $con->executeQuery('SELECT * FROM '.constant($table.'Peer::TABLE_NAME'));

      $dumpData[$table] = array();

      while ($rs->next()) {
        $pk = '';
        foreach ($tableMap->getColumns() as $column)
        {
          $col = strtolower($column->getColumnName());

          if ($column->isPrimaryKey())
          {
            $pk .= '_' .$rs->get($col);
            continue;
          }
          else if ($column->isForeignKey())
          {
            $relatedTable = $this->maps[$table]->getDatabaseMap()->getTable($column->getRelatedTableName());

            $dumpData[$table][$table.$pk][$col] = $relatedTable->getPhpName().'_'.$rs->get($col);
          }
          else
          {
            $dumpData[$table][$table.$pk][$col] = $rs->get($col);
          }
        } // foreach
      } // while
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
