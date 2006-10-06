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
  protected
    $singularName  = '',
    $pluralName    = '',
    $peerClassName = '',
    $map           = null,
    $tableMap      = null,
    $primaryKey    = array(),
    $className     = '',
    $params        = array();

  public function generate($params = array())
  {
    $this->params = $params;

    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
    {
      if (!isset($this->params[$entry]))
      {
        $error = 'You must specify a "%s"';
        $error = sprintf($error, $entry);

        throw new sfParseException($error);
      }
    }

    $modelClass = $this->params['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($this->params['moduleName']));
    $this->setModuleName($this->params['moduleName']);

    // get some model metadata
    $this->loadMapBuilderClasses();

    // load all primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($this->params['theme']) ? $this->params['theme'] : 'default';
    $themeDir = sfLoader::getGeneratorTemplate($this->getGeneratorClass(), $theme, '');
    if (!is_dir($themeDir))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    #$this->generatePhpFiles($this->generatedModuleName, array('listSuccess.php', 'editSuccess.php', 'showSuccess.php'));
    $templateFiles = sfFinder::type('file')->name('*.php')->relative()->in($themeDir.'/templates');
    
    $this->generatePhpFiles($this->generatedModuleName, $templateFiles);

    // require generated action class
    $data = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php');\n";

    return $data;
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
      $params[]  = "$fieldName='.".$this->getColumnGetter($pk, true, $prefix);
    }

    return implode(".'&", $params);
  }

  public function getPrimaryKeyIsSet($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = $this->getColumnGetter($pk, true, $prefix);
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
    
    $columnGetter = $this->getColumnGetter($column, true);

    if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP)
    {
      return "format_date($columnGetter, 'f')";
    }
    else
    {
      return "$columnGetter";
    }
  }

  public function getColumnEditTag($column, $params = array())
  {
    $type = $column->getCreoleType();

    if ($column->isForeignKey())
    {
      $params = $this->getObjectTagParams($params, array('related_class' => $this->getRelatedClassName($column)));
      return $this->getPHPObjectHelper('select_tag', $column, $params);
    }
    else if ($type == CreoleTypes::DATE)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true));
      return $this->getPHPObjectHelper('input_date_tag', $column, $params);
    }
    else if ($type == CreoleTypes::TIMESTAMP)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'withtime' => true));
      return $this->getPHPObjectHelper('input_date_tag', $column, $params);
    }
    else if ($type == CreoleTypes::BOOLEAN)
    {
      $params = $this->getObjectTagParams($params);
      return $this->getPHPObjectHelper('checkbox_tag', $column, $params);
    }
    else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
    {
      $size = ($column->getSize() > 20 ? ($column->getSize() < 80 ? $column->getSize() : 80) : 20);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return $this->getPHPObjectHelper('input_tag', $column, $params);
    }
    else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return $this->getPHPObjectHelper('input_tag', $column, $params);
    }
    else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return $this->getPHPObjectHelper('input_tag', $column, $params);
    }
    else if ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
    {
      $params = $this->getObjectTagParams($params, array('size' => '30x3'));
      return $this->getPHPObjectHelper('textarea_tag', $column, $params);
    }
    else
    {
      $params = $this->getObjectTagParams($params, array('disabled' => true));
      return $this->getPHPObjectHelper('input_tag', $column, $params);
    }
  }

  // here come the propel specific functions

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
      $getter = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $getter);
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
