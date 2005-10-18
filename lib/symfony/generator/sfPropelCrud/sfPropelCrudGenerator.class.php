<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
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
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
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

    $retval = $this->generateClass($actions);

    // save actions class
    $generator->getCache()->set('actions.class.php', $this->generatedModuleName.DIRECTORY_SEPARATOR.'actions', $retval);

    // generate template files
    $templates = array('listSuccess', 'editSuccess', 'showSuccess');
    foreach ($templates as $template)
    {
      // get some model metadata
      $c = $this->className;

      $class_map_builder = $c.'MapBuilder';
      require_once('model/'.$c.'.php');
      $map = new $class_map_builder();
      if (!$map->isBuilt())
      {
        $map->doBuild();
      }
      $table = $map->getDatabaseMap()->getTable(constant($c.'Peer::TABLE_NAME'));

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

      $retval = $this->generateTemplate($content);

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

    $action = "  public function executeShow ()\n".
              "  {\n".
              "    \$this->$s = $c"."Peer::retrieveByPk(\$this->getRequestParameter('id'));\n".
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

    $action = "  private function get{$c}OrCreate (\$name = 'id')\n".
              "  {\n".
              "    if (!\$this->getRequestParameter(\$name, 0))\n".
              "    {\n".
              "      \$$s = new $c();\n".
              "    }\n".
              "    else\n".
              "    {\n".
              "      \$$s = $c"."Peer::retrieveByPk(\$this->getRequestParameter(\$name));\n".
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
              "    return \$this->redirect('/".$this->moduleName."/show?id='.\${$s}->getId());\n".
              "  }\n";

    return $action;
  }

  public function getDeleteMethod()
  {
    $s = $this->singularName;
    $p = $this->pluralName;
    $c = $this->className;

    $action = "  public function executeDelete ()\n".
              "  {\n".
              "    \$$s = $c"."Peer::retrieveByPk(\$this->getRequestParameter('id'));\n".
              "\n".
              "    \$this->forward404_unless(\$$s instanceof $c);\n".
              "\n".
              "    \${$s}->delete();\n".
              "\n".
              "    return \$this->redirect('/".$this->moduleName."/list');\n".
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
    $this->singularName = sfInflector::underscore($className);
    $this->pluralName = $this->singularName.'s';
    $this->className = $className;
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
}

?>