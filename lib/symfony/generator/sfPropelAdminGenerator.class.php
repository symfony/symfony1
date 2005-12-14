<?php

/*

- gestion des aides sur les champs
- breadcrumb
- pagination des listes
- many to many (ajax?)
- edition des tables liées en inline ou tabular (cf. django)
- module d'authentification avec page de login toute faite (paramètre: classes à utiliser pour les utilisateurs)
- module de gestion des images (sfMedia...)
- mettre de la doc phpdoc dans les fichiers générées -> PDF automatique / html OK
- sortable en ajax (colonne à déclarer)
- possibilité d'avoir une colonne non existante avec get et set ou que get ou que set (password)
- autocomplete (pour des listes longues -> choix d'un utilisateur par exemple à la place d'un select)
  avec erreur si existe pas en BDD au retour!!! (ou champ caché user_id_real)
- possibilité de choisir les boutons : delete ou pas, ben c'est tout!
- filtres (avec filtres prédéfinis ?)
- gestion de tous les termes génériques en I18N (Edition d'un %s, Création d'un %s, ...)
OK - gestion de la validation des formulaires
- generateur spécifique pour gérer la home page et aggréger les modules générés et les autres
- layout spécifique + CSS spécifique (thème ?)
- gestion des types enums en passant un paramètre value

*/

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelAdminGenerator extends sfPropelCrudGenerator
{
  private
    $params = array(),
    $fields = array();

  public function initialize($generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelAdmin');
  }

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
    $this->setGeneratedModuleName('auto'.ucfirst($params['moduleName']));
    $this->setModuleName($params['moduleName']);

    // get some model metadata
    $this->loadMapBuilderClasses();

    // load all primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($this->params['theme']) ? $this->params['theme'] : 'default';
    if (!is_dir(SF_SYMFONY_DATA_DIR.'/symfony/generator/sfPropelAdmin/'.$theme.'/template'))
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

  public function getColumnEditTag($column, $params = array())
  {
    // user defined parameters
    $user_params = $this->getFieldProperty($column->getName(), 'params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params = $user_params ? array_merge($params, $user_params) : $params;

    // user sets a specific tag to use
    if ($type = $this->getFieldProperty($column->getName(), 'type'))
    {
      $params = $this->getObjectTagParams($params);
      return "object_$type(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }

    // guess the best tag to use with column type
    return parent::getColumnEditTag($column, $params);
  }

  public function getColumnCategories($paramName)
  {
    if (is_array($this->getParam($paramName)))
    {
      $fields = $this->getParam($paramName);

      // do we have categories?
      if (!isset($fields[0]))
      {
        return array_keys($fields);
      }

    }

    return array('NONE');
  }

  /**
   * returns an array of adminColumn objects
   * from the $paramName list or the list of all columns (in table) if it does not exist
   */
  public function getColumns($paramName, $category = 'NONE')
  {
    $phpNames = array();

    // user has set a personnalized list of fields?
    if (is_array($this->getParam($paramName)))
    {
      $fields = $this->getParam($paramName);

      // do we have categories?
      if (isset($fields[0]))
      {
        // simulate a default one
        $fields = array('NONE' => $fields);
      }

      foreach ($fields[$category] as $field)
      {
        $found = false;

        $phpName = sfInflector::camelize($field);

        // search the matching column for this column name
        foreach ($this->getTableMap()->getColumns() as $column)
        {
          if ($column->getPhpName() == $phpName)
          {
            $found = true;
            $phpNames[] = new adminColumn($column->getPhpName(), $column);
            break;
          }
        }

        // not a "real" column, so we simulate one
        if (!$found)
        {
          $phpNames[] = new adminColumn($phpName);
        }
      }
    }
    else
    {
      // no, just return the full list of columns in table
      foreach ($this->getTableMap()->getColumns() as $column)
      {
        $phpNames[] = new adminColumn($column->getPhpName(), $column);
      }
    }

    return $phpNames;
  }

  public function isLinkedColumn($column)
  {
    $links = $this->getParam('list_links');
    if (!$links)
    {
      return $column->isPrimaryKey();
    }

    if (in_array($column->getName(), $links))
    {
      return true;
    }

    return false;
  }

  public function getFieldName($name)
  {
    $retval = $this->getFieldProperty($name, 'name');
    if (!$retval)
    {
      $retval = sfInflector::humanize($name);
    }

    return $retval;
  }

  public function getFieldProperty($name, $property)
  {
    if (isset($this->params['fields'][$name]) && isset($this->params['fields'][$name][$property]))
    {
      return $this->params['fields'][$name][$property];
    }
    else
    {
      return null;
    }
  }

  public function getParam($name)
  {
    return isset($this->params[$name]) ? $this->params[$name] : null;
  }
}

class adminColumn
{
  private
    $phpName    = '',
    $column     = null;

  public function __construct($phpName, $column = null)
  {
    $this->phpName    = $phpName;
    $this->column     = $column;
  }

  public function __call($name, $arguments)
  {
    return $this->column ? $this->column->$name() : null;
  }

  public function getPhpName()
  {
    return $this->phpName;
  }

  public function getName()
  {
    return sfInflector::underscore($this->phpName);
  }
}

?>