<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Propel admin generator.
 *
 * This class executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */
class sfPropelAdminGenerator extends sfGenerator
{
  public function generate($generator, $class, $param)
  {
    $required_parameters = array('model_class', 'table');
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
      $error = 'Unable to generate admin module for unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    // generated module name
    $moduleName = $param['moduleName'];
    $generatedModuleName = 'auto'.ucfirst($moduleName);

    // generate actions class
    $actions = "class {$generatedModuleName}"."Actions extends sfActions\n".
               "{\n".
               "}\n";

    $retval = $this->generateClass($actions);

    // save actions class
    $generator->getCache()->set('actions.class.php', $generatedModuleName.DIRECTORY_SEPARATOR.'actions', $retval);
/*
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
      require(dirname(__FILE__).'/templates/'.$template.'.php');
      $content = ob_get_clean();

      // replace object names
      $content = str_replace('$objects',  '$'.$this->pluralName,   $content);
      $content = str_replace('$object',   '$'.$this->singularName, $content);
      $content = str_replace('ClassName', '$'.$this->className,    $content);

      $content = $this->replacehpMarks($content);

      $retval = $this->generateTemplate($content);

      // save actions class
      $generator->getCache()->set($template.'.php', $generatedModuleName.DIRECTORY_SEPARATOR.'templates', $retval);
    }
*/
    // require generated action class
    $data = "require_once(SF_MODULE_CACHE_DIR.'/{$generatedModuleName}/actions/actions.class.php')\n";

    return $data;
  }
}

?>