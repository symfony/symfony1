<?php

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
    $singularName        = '',
    $pluralName          = '',
    $peerClassName       = '',
    $map                 = null,
    $tableMap            = null,
    $primaryKey          = array(),
    $className           = '';

  public function generate($class, $param)
  {
    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
    {
      if (!isset($param[$entry]))
      {
        $error = 'You must specify a "%s"';
        $error = sprintf($error, $entry);

        throw new sfParseException($error);
      }
    }

    $modelClass = $param['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($param['moduleName']));
    $this->setModuleName($param['moduleName']);

    // get some model metadata
    $c = $this->className;

    // we must load all map builder classes to be able to deal with foreign keys (cf. editSuccess.php template)
    $classes = pakeFinder::type('file')->name('*MapBuilder.php')->relative()->in('lib/model');
    foreach ($classes as $class)
    {
      $class_map_builder = basename($class, '.php');
      require_once('model/'.$class);
      $maps[$class] = new $class_map_builder();
      if (!$maps[$class]->isBuilt())
      {
        $maps[$class]->doBuild();
      }

      if ($c == str_replace('MapBuilder', '', $class_map_builder))
      {
        $this->map = $maps[$class];
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

    // template directory
    $template_dir = SF_SYMFONY_DATA_DIR.'/generator/sfPropelCrud/template';

    $this->generatePhpFiles($this->generatedModuleName, $template_dir);

    // require generated action class
    $data = "require_once(SF_MODULE_CACHE_DIR.'/{$this->generatedModuleName}/actions/actions.class.php')\n";

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
}

?>