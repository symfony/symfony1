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

/**
 * Evaluates and echoes a component slot.
 * The component name is deduced from the definition of the view.yml
 * For a variable to be accessible to the component and its partial, 
 * it has to be passed in the second argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_component_slot('sidebar', array('myvar' => 12345));
 * </code>
 *
 * @param  string slot name
 * @param  array variables to be made accessible to the component
 *
 * @see    get_component_slot, include_partial, include_component
 */
function include_component_slot($name, $vars = array())
{
  echo get_component_slot($name, $vars);
}

/**
 * Evaluates and returns a component slot.
 * The syntax is similar to the one of include_component_slot.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_component_slot('sidebar', array('myvar' => 12345));
 * </code>
 *
 * @param  string slot name
 * @param  array variables to be made accessible to the component
 * @return string result of the component execution
 * @see    get_component_slot, include_partial, include_component
 */
function get_component_slot($name, $vars = array())
{
  $viewInstance = sfContext::getInstance()->get('view_instance');

  if (!$viewInstance->hasComponentSlot($name))
  {
    // cannot find component slot
    throw new sfConfigurationException(sprintf('The component slot "%s" is not set.', $name));
  }

  if ($componentSlot = $viewInstance->getComponentSlot($name))
  {
    return get_component($componentSlot[0], $componentSlot[1], $vars);
  }
}

/**
 * Evaluates and echoes a component.
 * For a variable to be accessible to the component and its partial, 
 * it has to be passed in the third argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_component('mymodule', 'mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string module name
 * @param  string component name
 * @param  array variables to be made accessible to the component
 *
 * @see    get_component, include_partial, include_component_slot
 */
function include_component($moduleName, $componentName, $vars = array())
{
  echo get_component($moduleName, $componentName, $vars);
}

/**
 * Evaluates and returns a component.
 * The syntax is similar to the one of include_component.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_component('mymodule', 'mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string module name
 * @param  string component name
 * @param  array variables to be made accessible to the component
 * @return string result of the component execution
 * @see    include_component
 */
function get_component($moduleName, $componentName, $vars = array())
{
  $context = sfContext::getInstance();
  $actionName = '_'.$componentName;

  // check cache
  if ($cacheManager = $context->getViewCacheManager())
  {
    $cacheManager->registerConfiguration($moduleName);
    $uri = '@sf_cache_partial?module='.$moduleName.'&action='.$actionName.'&sf_cache_key='.(isset($vars['sf_cache_key']) ? $vars['sf_cache_key'] : md5(serialize($vars)));
    if ($retval = _get_cache($cacheManager, $uri))
    {
      return $retval;
    }
  }

  $controller = $context->getController();

  if (!$controller->componentExists($moduleName, $componentName))
  {
    // cannot find component
    throw new sfConfigurationException(sprintf('The component does not exist: "%s", "%s".', $moduleName, $componentName));
  }

  // create an instance of the action
  $componentInstance = $controller->getComponent($moduleName, $componentName);

  // load component's module config file
  require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));

  $componentInstance->getVarHolder()->add($vars);

  // dispatch component
  $componentToRun = 'execute'.ucfirst($componentName);
  if (!method_exists($componentInstance, $componentToRun))
  {
    if (!method_exists($componentInstance, 'execute'))
    {
      // component not found
      throw new sfInitializationException(sprintf('sfComponent initialization failed for module "%s", component "%s".', $moduleName, $componentName));
    }

    $componentToRun = 'execute';
  }

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getEventDispatcher()->notify(new sfEvent(null, 'application.log', array(sprintf('Call "%s->%s()'.'"', $moduleName, $componentToRun))));
  }

  // run component
  if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
  {
    $timer = sfTimerManager::getTimer(sprintf('Component "%s/%s"', $moduleName, $componentName));
  }

  $retval = $componentInstance->$componentToRun();

  if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
  {
    $timer->addTime();
  }

  if ($retval != sfView::NONE)
  {
    // render
    $view = new sfPartialView($context, $moduleName, $actionName, '');
    $view->getAttributeHolder()->add($componentInstance->getVarHolder()->getAll());

    $retval = $view->render();

    if ($cacheManager)
    {
      $retval = _set_cache($cacheManager, $uri, $retval);
    }

    return $retval;
  }
}

/**
 * Evaluates and echoes a partial.
 * The partial name is composed as follows: 'mymodule/mypartial'.
 * The partial file name is _mypartial.php and is looked for in modules/mymodule/templates/.
 * If the partial name doesn't include a module name,
 * then the partial file is searched for in the caller's template/ directory.
 * If the module name is 'global', then the partial file is looked for in myapp/templates/.
 * For a variable to be accessible to the partial, it has to be passed in the second argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_partial('mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string partial name
 * @param  array variables to be made accessible to the partial
 *
 * @see    get_partial, include_component
 */
function include_partial($templateName, $vars = array())
{
  echo get_partial($templateName, $vars);
}

/**
 * Evaluates and returns a partial.
 * The syntax is similar to the one of include_partial
 *
 * <b>Example:</b>
 * <code>
 *  echo get_partial('mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string partial name
 * @param  array variables to be made accessible to the partial
 * @return string result of the partial execution
 * @see    include_partial
 */
function get_partial($templateName, $vars = array())
{
  $context = sfContext::getInstance();

  // partial is in another module?
  if (false !== $sep = strpos($templateName, '/'))
  {
    $moduleName   = substr($templateName, 0, $sep);
    $templateName = substr($templateName, $sep + 1);
  }
  else
  {
    $moduleName = $context->getActionStack()->getLastEntry()->getModuleName();
  }
  $actionName = '_'.$templateName;

  if ($cacheManager = $context->getViewCacheManager())
  {
    $cacheManager->registerConfiguration($moduleName);
    $uri = '@sf_cache_partial?module='.$moduleName.'&action='.$actionName.'&sf_cache_key='.(isset($vars['sf_cache_key']) ? $vars['sf_cache_key'] : md5(serialize($vars)));
    if ($retval = _get_cache($cacheManager, $uri))
    {
      return $retval;
    }
  }

  $view = new sfPartialView($context, $moduleName, $actionName, '');
  $view->getAttributeHolder()->add($vars);

  $retval = $view->render();

  if ($cacheManager)
  {
    $retval = _set_cache($cacheManager, $uri, $retval);
  }

  return $retval;
}

function _get_cache($cacheManager, $uri)
{
  $retval = $cacheManager->get($uri);

  if (sfConfig::get('sf_web_debug'))
  {
    $retval = sfContext::getInstance()->get('sf_web_debug')->decorateContentWithDebug($uri, $retval, false);
  }

  return $retval;
}

function _set_cache($cacheManager, $uri, $retval)
{
  $saved = $cacheManager->set($retval, $uri);

  if ($saved && sfConfig::get('sf_web_debug'))
  {
    $retval = sfContext::getInstance()->get('sf_web_debug')->decorateContentWithDebug($uri, $retval, true);
  }

  return $retval;
}

/**
 * Begins the capturing of the slot.
 *
 * @param  string slot name
 *
 * @see    end_slot
 */
function slot($name)
{
  $context = sfContext::getInstance();
  $response = $context->getResponse();

  $slot_names = sfConfig::get('symfony.view.slot_names', array());
  if (in_array($name, $slot_names))
  {
    throw new sfCacheException(sprintf('A slot named "%s" is already started.', $name));
  }

  $slot_names[] = $name;

  $response->setSlot($name, '');
  sfConfig::set('symfony.view.slot_names', $slot_names);

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getEventDispatcher()->notify(new sfEvent(null, 'application.log', array(sprintf('Set slot "%s"', $name))));
  }

  ob_start();
  ob_implicit_flush(0);
}

/**
 * Stops the content capture and save the content in the slot.
 *
 * @see    slot
 */
function end_slot()
{
  $content = ob_get_clean();

  $response = sfContext::getInstance()->getResponse();
  $slot_names = sfConfig::get('symfony.view.slot_names', array());
  if (!$slot_names)
  {
    throw new sfCacheException('No slot started.');
  }

  $name = array_pop($slot_names);

  $response->setSlot($name, $content);
  sfConfig::set('symfony.view.slot_names', $slot_names);
}

/**
 * Returns true if the slot exists.
 *
 * @param  string slot name
 * @return boolean true, if the slot exists
 * @see    get_slot, include_slot
 */
function has_slot($name)
{
  return array_key_exists($name, sfContext::getInstance()->getResponse()->getSlots());
}

/**
 * Evaluates and echoes a slot.
 *
 * <b>Example:</b>
 * <code>
 *  include_slot('navigation');
 * </code>
 *
 * @param  string slot name
 *
 * @see    has_slot, get_slot
 */
function include_slot($name)
{
  $context = sfContext::getInstance();
  $slots = $context->getResponse()->getSlots();

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getEventDispatcher()->notify(new sfEvent(null, 'application.log', array(sprintf('Get slot "%s"', $name))));
  }

  if (isset($slots[$name]))
  {
    echo $slots[$name];

    return true;
  }
  else
  {
    return false;
  }
}

/**
 * Evaluates and returns a slot.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_slot('navigation');
 * </code>
 *
 * @param  string slot name
 * @return string content of the slot
 * @see    has_slot, include_slot
 */
function get_slot($name)
{
  $context = sfContext::getInstance();
  $slots = $context->getResponse()->getSlots();

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getEventDispatcher()->notify(new sfEvent(null, 'application.log', array(sprintf('Get slot "%s"', $name))));
  }

  return isset($slots[$name]) ? $slots[$name] : '';
}
