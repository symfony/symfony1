<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HelperHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 * <b>DEPRECATED:</b> use use_helper() instead with the same syntax. 
 */ 
function use_helpers()
{
  if (sfConfig::get('sf_logging_active')) sfContext::getInstance()->getLogger()->err('The function "use_helpers()" is deprecated. Please use "use_helper()"'); 

  foreach (func_get_args() as $helperName)
  {
    use_helper($helperName);
  }
}

function use_helper()
{
  static $loaded = array();

  $dirs = array(
    sfConfig::get('sf_app_module_dir').'/'.sfContext::getInstance()->getModuleName().'/'.sfConfig::get('sf_app_module_lib_dir_name').'/helper', // module dir
    sfConfig::get('sf_app_lib_dir').'/helper',                                                                                                  // application dir
    sfConfig::get('sf_plugin_lib_dir').'/symfony/plugins/helper',                                                                               // plugin dir
    sfConfig::get('sf_symfony_lib_dir').'/helper',                                                                                              // global dir
  );

  foreach (func_get_args() as $helperName)
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
        include_once($dir.'/'.$fileName);
        $included = true;
        break;
      }
    }

    if (!$included)
    {
      // search in the include path
      if ((@include_once('helper/'.$fileName)) != 1)
      {
        throw new sfViewException(sprintf('Unable to load "%s" helper in: %s', $helperName, implode(', ', array_merge($dirs, explode(PATH_SEPARATOR, get_include_path())))));
      }
    }

    $loaded[$helperName] = true;
  }
}
