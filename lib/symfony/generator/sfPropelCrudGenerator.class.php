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
 * Propel CRUD generator.
 *
 * This class executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelCrudGenerator extends sfGenerator
{
  private
    $singularName  = '',
    $pluralName    = '',
    $peerClassName = '',
    $map           = null,
    $tableMap      = null,
    $primaryKey    = array(),
    $className     = '';

  public function initialize($generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelCrud');
  }

  public function generate($params = array())
  {
    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
    {
      if (!isset($params[$entry]))
      {
        $error = 'You must specify a "%s"';
        $error = sprintf($error, $entry);

        throw new sfParseException($error);
      }
    }

    $modelClass = $params['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($params['moduleName']));
    $this->setModuleName($params['moduleName']);

    // get some model metadata
    $c = $this->className;

    // we must load all map builder classes to be able to deal with foreign keys (cf. editSuccess.php template)
    $classes = pakeFinder::type('file')->name('*MapBuilder.php')->relative()->in(defined('SF_LIB_DIR') ? SF_LIB_DIR.'/model' : 'lib/model');
    foreach ($classes as $class)
    {
      $class_map_builder = basename($class, '.php');
      require_once('model/map/'.$class_map_builder.'.php');
      $maps[$class_map_builder] = new $class_map_builder();
      if (!$maps[$class_map_builder]->isBuilt())
      {
        $maps[$class_map_builder]->doBuild();
      }

      if ($c == str_replace('MapBuilder', '', $class_map_builder))
      {
        $this->map = $maps[$class_map_builder];
      }
    }
    $this->tableMap = $this->map->getDatabaseMap()->getTable(constant($c.'Peer::TABLE_NAME'));

    // get all primary keys
    foreach ($this->tableMap->getColumns() as $column)
    {
      if ($column->isPrimaryKey())
      {
        $this->primaryKey[] = $column;
      }
    }

    // theme exists?
    $theme = isset($params['theme']) ? $params['theme'] : 'default';
    if (!is_dir(SF_SYMFONY_DATA_DIR.'/symfony/generator/sfPropelCrud/'.$theme.'/template'))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    $this->generatePhpFiles($this->generatedModuleName);

    // require generated action class
    $data = "require_once(SF_MODULE_CACHE_DIR.'/".$this->generatedModuleName."/actions/actions.class.php')\n";

    return $data;
  }

  public function getRetrieveByPkParamsForShow()
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "\$this->getRequestParameter('".$this->translateFieldName($pk->getPhpName())."')";
    }

    return implode(",\n".str_repeat(' ', 49 - strlen($this->singularName.$this->className)), $params);
  }

  public function getMethodParamsForGetOrCreate()
  {
    $method_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName       = $this->translateFieldName($pk->getPhpName());
      $method_params[] = "\$$fieldName = '$fieldName'";
    }

    return implode(', ', $method_params);
  }

  public function getTestPksForGetOrCreate()
  {
    $test_pks = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName  = $this->translateFieldName($pk->getPhpName());
      $test_pks[] = "!\$this->getRequestParameter(\$$fieldName, 0)";
    }

    return implode("\n     || ", $test_pks);
  }

  public function getRetrieveByPkParamsForGetOrCreate()
  {
    $retrieve_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName         = $this->translateFieldName($pk->getPhpName());
      $retrieve_params[] = "\$this->getRequestParameter(\$$fieldName)";
    }

    return implode(",\n".str_repeat(' ', 45 - strlen($this->singularName.$this->className)), $retrieve_params);
  }

  public function getRetrieveByPkParamsForDelete()
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "\$this->getRequestParameter('".$this->translateFieldName($pk->getPhpName())."')";
    }

    $sep = ",\n".str_repeat(' ', 43 - strlen($this->singularName.$this->className));

    return implode($sep, $params);
  }

  public function getTableMap()
  {
    return $this->tableMap;
  }

  /**
   * Sets the class name to use for scaffolding
   *
   * @param  string class name
   */
  protected function setScaffoldingClassName($className)
  {
    $this->singularName  = sfInflector::underscore($className);
    $this->pluralName    = $this->singularName.'s';
    $this->className     = $className;
    $this->peerClassName = $className.'Peer';
  }

  /**
   * Gets the singular name for current scaffolding class.
   *
   * @return string
   */
  public function getSingularName()
  {
    return $this->singularName;
  }

  /**
   * Gets the plural name for current scaffolding class.
   *
   * @return string
   */
  public function getPluralName()
  {
    return $this->pluralName;
  }

  /**
   * Gets the class name for current scaffolding class.
   *
   * @return string
   */
  public function getClassName()
  {
    return $this->className;
  }

  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  public function translateFieldName($name, $fromType = 'phpName', $toType = 'fieldName')
  {
    return call_user_func(array($this->className, 'translateFieldName'), $name, $fromType, $toType);
  }

  public function getPrimaryKeyUrlParams()
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName   = $pk->getPhpName();
      $fieldName = $this->translateFieldName($phpName);
      $params[]  = "$fieldName='.\$".$this->singularName."->get$phpName()";
    }

    return implode(".'&", $params);
  }

  public function getPrimaryKeyIsSet()
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName  = $pk->getPhpName();
      $params[] = "\$".$this->singularName."->get$phpName()";
    }

    return implode(' && ', $params);
  }

  public function getColumnEditTag($column)
  {
    $type = $column->getCreoleType();
    if ($column->isForeignKey())
    {
      $relatedTable = $this->map->getDatabaseMap()->getTable($column->getRelatedTableName()); 
      return "object_select_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('related_class' => '{$relatedTable->getPhpName()}'))";
    }
    else if ($type == CreoleTypes::DATE)
    {
      // rich=false not yet implemented
      return "object_input_date_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('rich' => true))";
    }
    else if ($type == CreoleTypes::BOOLEAN)
    {
      return "object_checkbox_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}')";
    }
    else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR || $type == CreoleTypes::LONGVARCHAR)
    {
      $size = ($column->getSize() > 20 ? ($column->getSize() < 80 ? $column->getSize() : 80) : 20);
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('size' => $size))";
    }
    else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    {
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('size' => 7))";
    }
    else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    {
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('size' => 7))";
    }
    else if ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
    {
      return "object_textarea_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}')";
    }
    else
    {
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', array('disabled' => true))";
    }
  }
}

?>