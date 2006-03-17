<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PartialHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function include_component_slot($name)
{
  $context = sfContext::getInstance();

  $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
  $viewInstance     = $actionStackEntry->getViewInstance();

  if (!$viewInstance->hasComponentSlot($name))
  {
    // cannot find component slot
    $error = 'The component slot "%s" is not set';
    $error = sprintf($error, $name);

    throw new sfConfigurationException($error);
  }

  $componentSlot = $viewInstance->getComponentSlot($name);

  include_component($componentSlot[0], $componentSlot[1]);
}

function include_component($moduleName, $componentName, $vars = array())
{
  $context    = sfContext::getInstance();
  $controller = $context->getController();

  if (!$controller->componentExists($moduleName, $componentName))
  {
    // cannot find component
    $error = 'The component does not exist: "%s", "%s"';
    $error = sprintf($error, $moduleName, $componentName);

    throw new sfConfigurationException($error);
  }

  // create an instance of the action
  $componentInstance = $controller->getComponent($moduleName, $componentName);

  // initialize the action
  if ($componentInstance->initialize($context))
  {
    $componentInstance->getVarHolder()->add($vars);

    // dispatch component
    $componentToRun = 'execute'.ucfirst($componentName);
    if (!method_exists($componentInstance, $componentToRun))
    {
      if (method_exists($componentInstance, 'execute'))
      {
        $componentToTun = 'execute';
      }
      else
      {
        // component not found
        $error = 'sfComponent initialization failed for module "%s", component "%s"';
        $error = sprintf($error, $moduleName, $componentName);
        throw new sfInitializationException($error);
      }
    }

    if (sfConfig::get('sf_logging_active')) $context->getLogger()->info('{sfComponent} call "'.$moduleName.'->'.$componentToRun.'()'.'"');

    // run component
    $retval = $componentInstance->$componentToRun();

    if ($retval != sfView::NONE)
    {
      // get component vars
      $componentVars = $componentInstance->getVarHolder()->getAll();

      // include partial
      include_partial($moduleName.'/'.$componentName, $componentVars);
    }
  }
  else
  {
    // component failed to initialize
    $error = 'Component initialization failed for module "%s", component "%s"';
    $error = sprintf($error, $moduleName, $componentName);

    throw new sfInitializationException($error);
  }
}

function include_partial($name, $vars = array())
{
  // partial is in another module?
  $sep = strpos($name, '/');
  if ($sep)
  {
    $type = substr($name, 0, $sep);
    $filename = '_'.substr($name, $sep + 1).'.php';
  }
  else
  {
    $type = '';
    $filename = '_'.$name.'.php';
  }

  $context = sfContext::getInstance();

  $lastActionEntry  = $context->getActionStack()->getLastEntry();
  $firstActionEntry = $context->getActionStack()->getFirstEntry();

  // global variables
  $vars['sf_context']      = $context;
  $vars['sf_params']       = $context->getRequest()->getParameterHolder();
  $vars['sf_request']      = $context->getRequest();
  $vars['sf_user']         = $context->getUser();
  $vars['sf_last_module']  = $lastActionEntry->getModuleName();
  $vars['sf_last_action']  = $lastActionEntry->getActionName();
  $vars['sf_first_module'] = $firstActionEntry->getModuleName();
  $vars['sf_first_action'] = $firstActionEntry->getActionName();

  if (sfConfig::get('sf_use_flash'))
  {
    $sf_flash = new sfParameterHolder();
    $sf_flash->add($context->getUser()->getAttributeHolder()->getAll('symfony/flash'));
    $vars['sf_flash'] = $sf_flash;
  }

  // local action variables
  $action = $context->getActionStack()->getLastEntry()->getActionInstance();
  if (method_exists($action, 'getVars'))
  {
    $vars = array_merge($vars, $action->getVars());
  }

  // render to client
  if ($sep && $type == 'global')
  {
    $partial = sfConfig::get('sf_app_template_dir').DIRECTORY_SEPARATOR.$filename;
  }
  else if ($sep)
  {
    $partial = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
  }
  else
  {
    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();
    $partial = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name').DIRECTORY_SEPARATOR.$current_module.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
  }

  if (!is_readable($partial))
  {
    $ok = false;

    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();

    // search partial for generated templates in cache
    $partial = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($current_module).'/templates/'.$filename;
    if (is_readable($partial))
    {
      $ok = true;
    }
    else
    {
      // search partial in a symfony module directory
      $partial = sfConfig::get('sf_symfony_data_dir').'/modules/'.$current_module.'/templates/'.$filename;
      if (is_readable($partial))
      {
        $ok = true;
      }
    }

    if (!$ok)
    {
      // the partial isn't readable
      $error = sprintf('The partial "%s" does not exist or is unreadable', $filename);
      throw new sfRenderException($error);
    }
  }

  extract($vars);
  require $partial;
}

?>