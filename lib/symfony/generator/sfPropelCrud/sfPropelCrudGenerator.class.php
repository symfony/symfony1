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
    $generatedModuleName = '',
    $moduleName          = '',
    $map                 = null,
    $tableMap            = null,
    $primaryKey          = array(),
    $className           = '';

  public function generate($generator, $class, $param)
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
    $moduleName = $param['moduleName'];
    $this->generatedModuleName = 'auto'.ucfirst($moduleName);
    $this->moduleName = $moduleName;

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

    // generate actions class
    $actions = "class {$this->generatedModuleName}"."Actions extends sfActions\n".
               "{\n".
               $this->getIndexMethod()."\n".
               $this->getListMethod()."\n".
               $this->getShowMethod()."\n".
               $this->getEditMethod()."\n".
               $this->getUpdateMethod()."\n".
               $this->getDeleteMethod()."\n".
               $this->getGetObjectOrCreate()."\n".
               "}\n";

    $retval = $this->generateClass($actions, __CLASS__);

    // save actions class
    $generator->getCache()->set('actions.class.php', $this->generatedModuleName.DIRECTORY_SEPARATOR.'actions', $retval);

    // generate template files
    $templates = array('listSuccess', 'editSuccess', 'showSuccess');
    foreach ($templates as $template)
    {
      // eval template template file
      ob_start();
      require(dirname(__FILE__).'/template/templates/'.$template.'.php');
      $content = ob_get_clean();

      // replace object names
      $content = str_replace('$objects',  '$'.$this->pluralName,   $content);
      $content = str_replace('$object',   '$'.$this->singularName, $content);
      $content = str_replace('ClassName', '$'.$this->className,    $content);

      // replace [?php and ?]
      $content = $this->replacePhpMarks($content);

      $retval = $this->generateTemplate($content, __CLASS__);

      // save actions class
      $generator->getCache()->set($template.'.php', $this->generatedModuleName.DIRECTORY_SEPARATOR.'templates', $retval);
    }

    // require generated action class
    $data = "require_once(SF_MODULE_CACHE_DIR.'/{$this->generatedModuleName}/actions/actions.class.php')\n";

    return $data;
  }

  public function getIndexMethod ()
  {
    $action = "  public function executeIndex ()\n".
              "  {\n".
              "    return \$this->forward('".$this->moduleName."', 'list');\n".
              "  }\n";

    return $action;
  }

  public function getListMethod ()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $action = "  public function executeList ()\n".
              "  {\n".
              "    \$this->$p = $c"."Peer::doSelect(new Criteria());\n".
              "  }\n";

    return $action;
  }

  public function getShowMethod()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "\$this->getRequestParameter('".$this->translateFieldName($pk->getPhpName())."')";
    }
    $param = implode(",\n".str_repeat(' ', 49 - strlen($c.$s)), $params);

    $action = "  public function executeShow ()\n".
              "  {\n".
              "    \$this->$s = $c"."Peer::retrieveByPk($param);\n".
              "\n".
              "    \$this->forward404_unless(\$this->$s instanceof $c);\n".
              "  }\n";

    return $action;
  }

  public function getEditMethod()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $action = "  public function executeEdit ()\n".
              "  {\n".
              "    \$this->$s = \$this->get{$c}OrCreate();\n".
              "  }\n";

    return $action;
  }

  public function getGetObjectOrCreate()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $function_params = array();
    $test_pks        = array();
    $retrieve_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName = $this->translateFieldName($pk->getPhpName());

      $function_params[] = "\$$fieldName = '$fieldName'";
      $test_pks[]        = "!\$this->getRequestParameter(\$$fieldName, 0)";
      $retrieve_params[] = "\$this->getRequestParameter(\$$fieldName)";
    }
    $function_param = implode(', ', $function_params);
    $test_pk        = implode("\n     || ", $test_pks);
    $retrieve_param = implode(",\n".str_repeat(' ', 45 - strlen($c.$s)), $retrieve_params);

    $action = "  private function get{$c}OrCreate ($function_param)\n".
              "  {\n".
              "    if ($test_pk)\n".
              "    {\n".
              "      \$$s = new $c();\n".
              "    }\n".
              "    else\n".
              "    {\n".
              "      \$$s = $c"."Peer::retrieveByPk($retrieve_param);\n".
              "\n".
              "      \$this->forward404_unless(\$$s instanceof $c);\n".
              "    }\n".
              "\n".
              "    return \$$s;\n".
              "  }\n";

    return $action;
  }

  public function getUpdateMethod()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $action = "  public function executeUpdate ()\n".
              "  {\n".
              "    \$$s = \$this->get{$c}OrCreate();\n".
              "\n".
              "    \${$s}->fromArray(\$this->getRequest()->getParameterHolder()->getAll(), $c"."::TYPE_FIELDNAME);\n".
              "    \${$s}->save();\n".
              "\n".
              "    return \$this->redirect('".$this->moduleName."/show?".$this->getPrimaryKeyUrlParams().");\n".
              "  }\n";

    return $action;
  }

  public function getDeleteMethod()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "\$this->getRequestParameter('".$this->translateFieldName($pk->getPhpName())."')";
    }

    $sep = ",\n".str_repeat(' ', 43 - strlen($c.$s));
    $pks = implode($sep, $params);
    $action = "  public function executeDelete ()\n".
              "  {\n".
              "    \$$s = $c"."Peer::retrieveByPk($pks);\n".
              "\n".
              "    \$this->forward404_unless(\$$s instanceof $c);\n".
              "\n".
              "    \${$s}->delete();\n".
              "\n".
              "    return \$this->redirect('".$this->moduleName."/list');\n".
              "  }\n";

    return $action;
  }

  /**
   * Sets the class name to use for scaffolding
   *
   * @param  string class name
   */
  public function setScaffoldingClassName($className)
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
  public function getSingularScaffoldName()
  {
    return $this->singularName;
  }

  /**
   * Gets the plural name for current scaffolding class.
   *
   * @return string
   */
  public function getPluralScaffoldName()
  {
    return $this->pluralName;
  }

  /**
   * Gets the class name for current scaffolding class.
   *
   * @return string
   */
  public function getClassScaffoldName()
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
      $params[]  = "$fieldName='.\$object->get$phpName()";
    }

    return implode(".'&", $params);
  }

  public function getPrimaryKeyIsSet()
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName  = $pk->getPhpName();
      $params[] = "\$object->get$phpName()";
    }

    return implode(' && ', $params);
  }
}

?>