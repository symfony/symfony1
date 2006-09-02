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
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLoader
{
  static public function getControllerDirs($moduleName)
  {
    $actionDir = sfConfig::get('sf_app_module_action_dir_name');
    return array(
      sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.$actionDir => false,          // application
      sfConfig::get('sf_plugin_data_dir').'/modules/'.$moduleName.'/'.$actionDir => true,  // local plugins
      sfConfig::get('sf_symfony_data_dir').'/modules/'.$moduleName.'/'.$actionDir => true, // core modules or global plugins
    );
  }

  static public function getTemplateDirs($appDir, $moduleName)
  {
    $templateDirs = sfConfig::get('sf_app_module_template_dir_name');
    return array(
      $appDir,                                                                             // application
      sfConfig::get('sf_plugin_data_dir').'/modules/'.$moduleName.'/'.$templateDirs,       // local plugins
      sfConfig::get('sf_symfony_data_dir').'/modules/'.$moduleName.'/'.$templateDirs,      // core modules or global plugins
      sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($moduleName).'/'.$templateDirs, // generated templates in cache
    );
  }

  static public function getConfigDirs($configPath)
  {
    $globalConfigPath = basename(dirname($configPath)).'/'.basename($configPath);

    return array(
      sfConfig::get('sf_symfony_data_dir').'/'.$globalConfigPath, // default symfony configuration
      sfConfig::get('sf_app_dir').'/'.$globalConfigPath,          // default project configuration
      sfConfig::get('sf_plugin_data_dir').'/'.$configPath,        // used for plugin modules
      sfConfig::get('sf_symfony_data_dir').'/'.$configPath,       // core modules or global plugins
      sfConfig::get('sf_root_dir').'/'.$globalConfigPath,         // used for main configuration
      sfConfig::get('sf_cache_dir').'/'.$configPath,              // used for generated modules
      sfConfig::get('sf_app_dir').'/'.$configPath,
    );
  }

  static public function getHelperDirs($moduleName = '')
  {
    $dirs = array(
      sfConfig::get('sf_app_lib_dir').'/helper',            // application dir
      sfConfig::get('sf_plugin_lib_dir').'/symfony/helper', // plugin dir
      sfConfig::get('sf_symfony_lib_dir').'/helper',        // global dir
    );

    if ($moduleName)
    {
      array_unshift($dirs, sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_lib_dir_name').'/helper'); // module dir
    }

    return $dirs;
  }

  static public function loadHelpers($helpers, $moduleName = '')
  {
    static $loaded = array();

    $dirs = self::getHelperDirs($moduleName);
    foreach ($helpers as $helperName)
    {
      if (isset($loaded[$helperName]))
      {
        continue;
      }

      $fileName = $helperName.'Helper.php';
      foreach ($dirs as $dir)
      {
        $included = false;
        if (is_readable($dir.'/'.$fileName))
        {
          include($dir.'/'.$fileName);
          $included = true;
          break;
        }
      }

      if (!$included)
      {
        // search in the include path
        if ((@include('helper/'.$fileName)) != 1)
        {
          throw new sfViewException(sprintf('Unable to load "%s" helper in: %s', $helperName, implode(', ', array_merge($dirs, explode(PATH_SEPARATOR, get_include_path())))));
        }
      }

      $loaded[$helperName] = true;
    }
  }
}
