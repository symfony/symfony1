<?php

require_once('pake/pakeFinder.class.php');

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
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
    $deleteCurrentDatas = true;

  public function setDeleteCurrentDatas($boolean)
  {
    $this->deleteCurrentDatas = $boolean;
  }

  public function getDeleteCurrentDatas()
  {
    return $this->deleteCurrentDatas;
  }

  // data/ -> name voir c2 test en tableaux
  // symfony load-data (file|dir) (option pour supprimer)
  // todo: symfony dump-data
  public function loadData($directory_or_file = null)
  {
    // directory or file?
    $fixture_files = array();
    if (!$directory_or_file)
    {
      $directory_or_file = SF_DATA_DIR.'/fixtures';
    }

    if (is_file($directory_or_file))
    {
      $fixture_files[] = $directory_or_file;
    }
    else if (is_dir($directory_or_file))
    {
      $fixture_files = pakeFinder::type('file')->name('*.yml')->in($directory_or_file);
    }
    else
    {
      throw new sfInitializationException('You must give a directory or file.');
    }

    $objects = array();

    // delete all current datas in database
    if ($this->deleteCurrentDatas)
    {
      rsort($fixture_files);
      foreach ($fixture_files as $fixture_file)
      {
        $main_datas = sfYaml::load($fixture_file);
        $classes = array_keys($main_datas);
        krsort($classes);
        foreach ($classes as $class)
        {
          $peer_class = $class.'Peer';

          call_user_func(array($peer_class, 'doDeleteAll'));
        }
      }
    }

    sort($fixture_files);
    $maps = array();
    foreach ($fixture_files as $fixture_file)
    {
      // import new datas
      $main_datas = sfYaml::load($fixture_file);
      foreach ($main_datas as $class => $datas)
      {
        $peer_class = $class.'Peer';

        // load map class
        $class_map_builder = $class.'MapBuilder';
        require_once('model/map/'.$class_map_builder.'.php');
        if (!isset($maps[$class]))
        {
          $maps[$class] = new $class_map_builder();
          $maps[$class]->doBuild();
        }
        $tableMap = $maps[$class]->getDatabaseMap()->getTable(constant($peer_class.'::TABLE_NAME'));

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
                $relatedTable = $maps[$class]->getDatabaseMap()->getTable($column->getRelatedTableName());
                $value = $objects[$relatedTable->getPhpName().'_'.$value];
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
            $objects[$class.'_'.$key] = $obj->getPrimaryKey();
          }
        }
      }
    }
  }
}

?>