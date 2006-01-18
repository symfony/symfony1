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
  private
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
  // todo: symfony dump-data
  public function loadData($directory_or_file = null)
  {
    $fixture_files = $this->getFiles($directory_or_file);

    // wrap all databases operations in a single transaction
    $con = sfContext::getInstance()->getDatabaseConnection();
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
    $main_datas = sfYaml::load($fixture_file);
    foreach ($main_datas as $class => $datas)
    {
      $class = trim($class);

      $peer_class = $class.'Peer';

      // load map class
      $this->loadMapBuilder($class);

      $tableMap = $this->maps[$class]->getDatabaseMap()->getTable(constant($peer_class.'::TABLE_NAME'));

      $column_names = call_user_func_array(array($peer_class, 'getFieldNames'), array(BasePeer::TYPE_FIELDNAME));

      // iterate through datas for this class
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
          else if (method_exists($obj, $method))
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
        $main_datas = sfYaml::load($fixture_file);
        $classes = array_keys($main_datas);
        krsort($classes);
        foreach ($classes as $class)
        {
          $peer_class = trim($class.'Peer');

          require_once(sfConfig::get('sf_model_lib_dir').'/'.$peer_class.'.php');

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

  private function loadMapBuilder($class)
  {
    $class_map_builder = $class.'MapBuilder';
    if (!isset($this->maps[$class]))
    {
      require_once(sfConfig::get('sf_model_lib_dir').'/map/'.$class_map_builder.'.php');
      $this->maps[$class] = new $class_map_builder();
      $this->maps[$class]->doBuild();
    }
  }
}

?>