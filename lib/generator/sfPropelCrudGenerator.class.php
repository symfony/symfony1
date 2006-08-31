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
    $this->loadMapBuilderClasses();

    // load all primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($params['theme']) ? $params['theme'] : 'default';
    if (!is_dir(sfConfig::get('sf_symfony_data_dir').'/generator/sfPropelCrud/'.$theme.'/template'))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    $this->generatePhpFiles($this->generatedModuleName, array('listSuccess.php', 'editSuccess.php', 'showSuccess.php'));

    // require generated action class
    $data = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php');\n";

    return $data;
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
  }

  protected function loadMapBuilderClasses()
  {
    // we must load all map builder classes to be able to deal with foreign keys (cf. editSuccess.php template)
    $classes = sfFinder::type('file')->name('*MapBuilder.php')->in(sfConfig::get('sf_lib_dir') ? sfConfig::get('sf_lib_dir') : 'lib');
    foreach ($classes as $class)
    {
      $class_map_builder = basename($class, '.php');
      require_once($class);
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

  public function getRetrieveByPkParamsForAction($indent)
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "\$this->getRequestParameter('".sfInflector::underscore($pk->getPhpName())."')";
    }

    return implode(",\n".str_repeat(' ', max(0, $indent - strlen($this->singularName.$this->className))), $params);
  }

  public function getMethodParamsForGetOrCreate()
  {
    $method_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName       = sfInflector::underscore($pk->getPhpName());
      $method_params[] = "\$$fieldName = '$fieldName'";
    }

    return implode(', ', $method_params);
  }

  public function getTestPksForGetOrCreate()
  {
    $test_pks = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName  = sfInflector::underscore($pk->getPhpName());
      $test_pks[] = "!\$this->getRequestParameter('$fieldName', 0)";
    }

    return implode("\n     || ", $test_pks);
  }

  public function getRetrieveByPkParamsForGetOrCreate()
  {
    $retrieve_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName         = sfInflector::underscore($pk->getPhpName());
      $retrieve_params[] = "\$this->getRequestParameter(\$$fieldName)";
    }

    return implode(",\n".str_repeat(' ', max(0, 45 - strlen($this->singularName.$this->className))), $retrieve_params);
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

  public function getPeerClassName()
  {
    return $this->peerClassName;
  }

  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  public function getMap()
  {
    return $this->map;
  }

  public function getPrimaryKeyUrlParams($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName   = $pk->getPhpName();
      $fieldName = sfInflector::underscore($phpName);
      $params[]  = "$fieldName='.\$".$prefix.$this->singularName."->get$phpName()";
    }

    return implode(".'&", $params);
  }

  public function getPrimaryKeyIsSet($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName  = $pk->getPhpName();
      $params[] = "\$".$prefix.$this->singularName."->get$phpName()";
    }

    return implode(' && ', $params);
  }

  protected function getObjectTagParams($params, $default_params = array())
  {
    return var_export(array_merge($default_params, $params), true);
  }

  public function getColumnListTag($column, $params = array())
  {
    $type = $column->getCreoleType();

    if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP)
    {
      return "format_date(\${$this->getSingularName()}->get{$column->getPhpName()}(), 'f')";
    }
    else
    {
      return "\${$this->getSingularName()}->get{$column->getPhpName()}()";
    }
  }

  public function getColumnEditTag($column, $params = array())
  {
    $type = $column->getCreoleType();

    if ($column->isForeignKey())
    {
      $relatedTable = $this->getMap()->getDatabaseMap()->getTable($column->getRelatedTableName());
      $params = $this->getObjectTagParams($params, array('related_class' => $relatedTable->getPhpName()));
      return "object_select_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::DATE)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true));
      return "object_input_date_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::TIMESTAMP)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'withtime' => true));
      return "object_input_date_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::BOOLEAN)
    {
      $params = $this->getObjectTagParams($params);
      return "object_checkbox_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
    {
      $size = ($column->getSize() > 20 ? ($column->getSize() < 80 ? $column->getSize() : 80) : 20);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else if ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
    {
      $params = $this->getObjectTagParams($params, array('size' => '30x3'));
      return "object_textarea_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
    else
    {
      $params = $this->getObjectTagParams($params, array('disabled' => true));
      return "object_input_tag(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }
  }
}
