<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfController directs application flow.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfController
{
  private
    $context                  = null,
    $controllerClasses        = array(),
    $maxForwards              = 5,
    $renderMode               = sfView::RENDER_CLIENT,
    $executionFilterClassName = null,
    $renderingFilterClassName = null,
    $viewCacheClassName       = null;

  /**
   * Removes current sfController instance
   *
   * This method only exists for testing purpose. Don't use it in your application code.
   */
  public static function removeInstance()
  {
    self::$instance = null;
  }

  /**
   * Indicates whether or not a module has a specific component.
   *
   * @param string A module name.
   * @param string An component name.
   *
   * @return bool true, if the component exists, otherwise false.
   */
  public function componentExists ($moduleName, $componentName)
  {
    return $this->controllerExists($moduleName, $componentName, 'component');
  }

  /**
   * Indicates whether or not a module has a specific action.
   *
   * @param string A module name.
   * @param string An action name.
   *
   * @return bool true, if the action exists, otherwise false.
   */
  public function actionExists ($moduleName, $actionName)
  {
    return $this->controllerExists($moduleName, $actionName, 'action');
  }

  private function controllerExists ($moduleName, $controllerName, $extension)
  {
    // all directories to look for modules
    $dirs = array(
      // application
      sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_action_dir_name') => false,

      // local plugin
      sfConfig::get('sf_plugin_data_dir').'/modules/'.$moduleName.'/actions' => true,

      // core modules or global plugins
      sfConfig::get('sf_symfony_data_dir').'/modules/'.$moduleName.'/actions' => true,
    );

    foreach ($dirs as $dir => $checkActivated)
    {
      // plugin module activated?
      if ($checkActivated && !in_array($moduleName, sfConfig::get('sf_activated_modules')) && is_readable($dir))
      {
        $error = 'The module "%s" is not activated.';
        $error = sprintf($error, $moduleName);

        throw new sfConfigurationException($error);
      }

      // one action per file or one file for all actions
      $classFile   = strtolower($extension);
      $classSuffix = ucfirst(strtolower($extension));
      $file        = $dir.'/'.$controllerName.$classSuffix.'.class.php';
      $module_file = $dir.'/'.$classFile.'s.class.php';
      if (is_readable($file))
      {
        // action class exists
        require_once($file);

        $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $controllerName.$classSuffix;

        return true;
      }
      else if (is_readable($module_file))
      {
        // module class exists
        require_once($module_file);

        // action is defined in this class?
        $defined = in_array(strtolower('execute'.$controllerName), array_map('strtolower', get_class_methods($moduleName.$classSuffix.'s')));
        if ($defined)
        {
          $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $moduleName.$classSuffix.'s';
        }

        return $defined;
      }
    }

    return false;
  }

  /**
   * Forward the request to another action.
   *
   * @param string  A module name.
   * @param string  An action name.
   * @param boolean Is this action is a slot context
   *
   * @return void
   *
   * @throws <b>sfConfigurationException</b> If an invalid configuration setting has been found.
   * @throws <b>sfForwardException</b> If an error occurs while forwarding the request.
   * @throws <b>sfInitializationException</b> If the action could not be initialized.
   * @throws <b>sfSecurityException</b> If the action requires security but the user implementation is not of type sfSecurityUser.
   */
  public function forward ($moduleName, $actionName, $isSlot = false)
  {
    // replace unwanted characters
    $moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);
    $actionName = preg_replace('/[^a-z0-9\-_]+/i', '', $actionName);

    if ($this->getActionStack()->getSize() >= $this->maxForwards)
    {
      // let's kill this party before it turns into cpu cycle hell
      $error = 'Too many forwards have been detected for this request (> %d)';
      $error = sprintf($error, $this->maxForwards);

      throw new sfForwardException($error);
    }

    if (!sfConfig::get('sf_available'))
    {
      // application is unavailable
      $moduleName = sfConfig::get('sf_unavailable_module');
      $actionName = sfConfig::get('sf_unavailable_action');

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find unavailable module/action
        $error = 'Invalid configuration settings: [sf_unavailable_module] "%s", [sf_unavailable_action] "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfConfigurationException($error);
      }
    }

    // check for a module generator config file
    sfConfigCache::getInstance()->import(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/generator.yml', true, true);

    if (!$this->actionExists($moduleName, $actionName))
    {
      // the requested action doesn't exist
      if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfController} action does not exist');

      // track the requested module so we have access to the data in the error 404 page
      $this->context->getRequest()->setAttribute('requested_action', $actionName);
      $this->context->getRequest()->setAttribute('requested_module', $moduleName);

      // switch to error 404 action
      $moduleName = sfConfig::get('sf_error_404_module');
      $actionName = sfConfig::get('sf_error_404_action');

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find unavailable module/action
        $error = 'Invalid configuration settings: [sf_error_404_module] "%s", [sf_error_404_action] "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfConfigurationException($error);
      }
    }

    // create an instance of the action
    $actionInstance = $this->getAction($moduleName, $actionName);

    // add a new action stack entry
    $this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance, $isSlot);

    // include module configuration
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));

    // check if this module is internal
    if ($this->getActionStack()->getSize() == 1 && sfConfig::get('mod_'.strtolower($moduleName).'_is_internal'))
    {
      $error = 'Action "%s" from module "%s" cannot be called directly';
      $error = sprintf($error, $actionName, $moduleName);

      throw new sfConfigurationException($error);
    }

    if (sfConfig::get('mod_'.strtolower($moduleName).'_enabled'))
    {
      // module is enabled

      // check for a module config.php
      $moduleConfig = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/config.php';
      if (is_readable($moduleConfig))
      {
        require_once($moduleConfig);
      }

      // initialize the action
      if ($actionInstance->initialize($this->context))
      {
        // create a new filter chain
        $filterChain = new sfFilterChain();

        if (sfConfig::get('sf_available'))
        {
          // the application is available so we'll register
          // global and module filters, otherwise skip them

          // does this action require security?
          if (sfConfig::get('sf_use_security') && $actionInstance->isSecure())
          {
            if (!in_array('sfSecurityUser', class_implements($this->context->getUser())))
            {
              // we've got security on but the user implementation
              // isn't a sub-class of SecurityUser
              $error = 'Security is enabled, but your User implementation isn\'t a sub-class of SecurityUser';
              throw new sfSecurityException($error);
            }

            // register security filter
            $filterChain->register($this->context->getSecurityFilter());
          }

          // load filters
          $this->loadGlobalFilters($filterChain);
          $this->loadModuleFilters($filterChain);
        }

        if (sfConfig::get('sf_web_debug'))
        {
          // register web debug toolbar filter
          $webDebugFilter = new sfWebDebugFilter();
          $webDebugFilter->initialize($this->context);
          $filterChain->register($webDebugFilter);
        }

        // register common HTTP filter
        $commonFilter = new sfCommonFilter();
        $commonFilter->initialize($this->context);
        $filterChain->register($commonFilter);

        if (sfConfig::get('sf_cache'))
        {
          // register cache filter
          $cacheFilter = new sfCacheFilter();
          $cacheFilter->initialize($this->context);
          $filterChain->register($cacheFilter);
        }

        if (sfConfig::get('sf_use_flash'))
        {
          // register flash filter
          $flashFilter = new sfFlashFilter();
          $flashFilter->initialize($this->context);
          $filterChain->register($flashFilter);
        }

        // register the execution filter
        $execFilter = new $this->executionFilterClassName();
        $execFilter->initialize($this->context);
        $filterChain->registerExecution($execFilter);

        // register the rendering filter
        $renderFilter = new $this->renderingFilterClassName();
        $renderFilter->initialize($this->context);
        $filterChain->registerRendering($renderFilter);

        if ($moduleName == sfConfig::get('sf_error_404_module') && $actionName == sfConfig::get('sf_error_404_action'))
        {
          $this->getContext()->getResponse()->setStatusCode(404);
          $this->getContext()->getResponse()->setHttpHeader('Status', '404 Not Found');
        }

        // change i18n message source directory to our module
        if (sfConfig::get('sf_i18n'))
        {
          $this->context->getI18N()->setMessageSourceDir(sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_i18n_dir_name'), $this->context->getUser()->getCulture());
        }

        // process the filter chain
        $filterChain->execute();
      }
      else
      {
        // action failed to initialize
        $error = 'Action initialization failed for module "%s", action "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfInitializationException($error);
      }
    }
    else
    {
      // module is disabled
      $moduleName = sfConfig::get('sf_module_disabled_module');
      $actionName = sfConfig::get('sf_module_disabled_action');

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find mod disabled module/action
        $error = 'Invalid configuration settings: [sf_module_disabled_module] "%s", [sf_module_disabled_action] "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfConfigurationException($error);
      }

      $this->forward($moduleName, $actionName);
    }
  }

  /**
   * Retrieve an Action implementation instance.
   *
   * @param  string A module name.
   * @param  string An action name.
   *
   * @return Action An Action implementation instance, if the action exists, otherwise null.
   */
  public function getAction ($moduleName, $actionName)
  {
    return $this->getController($moduleName, $actionName, 'action');
  }

  /**
   * Retrieve a Component implementation instance.
   *
   * @param  string A module name.
   * @param  string A component name.
   *
   * @return Component A Component implementation instance, if the component exists, otherwise null.
   */
  public function getComponent ($moduleName, $componentName)
  {
    return $this->getController($moduleName, $componentName, 'component');
  }

  private function getController ($moduleName, $controllerName, $extension)
  {
    $classSuffix = ucfirst(strtolower($extension));
    if (!isset($this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix]))
    {
      $this->controllerExists($moduleName, $controllerName, $extension);
    }

    $class = $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix];

    // fix for same name classes
    $moduleClass = $moduleName.'_'.$class;

    if (class_exists($moduleClass, false))
    {
      $class = $moduleClass;
    }

    return new $class();
  }

  /**
   * Retrieve the action stack.
   *
   * @return sfActionStack An sfActionStack instance, if the action stack is enabled, otherwise null.
   */
  public function getActionStack ()
  {
    return $this->context->getActionStack();
  }

  /**
   * Retrieve the current application context.
   *
   * @return Context A Context instance.
   */
  public function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfController A sfController implementation instance.
   *
   * @throws sfControllerException If a controller implementation instance has not been created.
   */
  public static function getInstance ()
  {
    $error = 'sfController::getInstance deprecated, use newInstance method instead.';
    throw new sfControllerException($error);

    if (isset(self::$instance))
    {
      return self::$instance;
    }

    // an instance of the controller has not been created
    $error = 'A sfController implementation instance has not been created';

    throw new sfControllerException($error);
  }

  /**
   * Retrieve the presentation rendering mode.
   *
   * @return int One of the following:
   *             - sfView::RENDER_CLIENT
   *             - sfView::RENDER_VAR
   */
  public function getRenderMode ()
  {
    return $this->renderMode;
  }

  /**
   * Retrieve a View implementation instance.
   *
   * @param string A module name.
   * @param string An action name.
   * @param string A view name.
   *
   * @return View A View implementation instance, if the view exists, otherwise null.
   */
  public function getView ($moduleName, $actionName, $viewName)
  {
    // user view exists?
    $file = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_view_dir_name').'/'.$viewName.'View.class.php';

    if (is_readable($file))
    {
      require_once($file);

      $class = $viewName.'View';

      // fix for same name classes
      $moduleClass = $moduleName.'_'.$class;

      if (class_exists($moduleClass, false))
      {
        $class = $moduleClass;
      }
    }
    else
    {
      // view class (as configured in module.yml or defined in action)
      $viewName = $this->getContext()->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', '', 'symfony/action/view') ? $this->getContext()->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', '', 'symfony/action/view') : sfConfig::get('mod_'.strtolower($moduleName).'_view_class');
      $file     = sfConfig::get('sf_symfony_lib_dir').'/view/'.$viewName.'View.class.php';
      $class    = is_readable($file) ? $viewName.'View' : 'sfPHPView';
    }

    return new $class();
  }

  /**
   * Indicates whether or not a module has a specific view.
   *
   * @param string A module name.
   * @param string An action name.
   * @param string A view name.
   *
   * @return bool true, if the view exists, otherwise false.
   */
  public function viewExists ($moduleName, $actionName, $viewName)
  {
    // view always exists in symfony
    return 1;
  }

  /**
   * Set the name of the ExecutionFilter class that is used in forward()
   *
   * @param string The class name of the ExecutionFilter to use
   *
   * @return void
   */
  public function setExecutionFilterClassName($className)
  {
    $this->executionFilterClassName = $className;
  }

  /**
   * Set the name of the RenderingFilter class that is used in forward()
   *
   * @param string The class name of the RenderingFilter to use
   *
   * @return void
   */
  public function setRenderingFilterClassName($className)
  {
    $this->renderingFilterClassName = $className;
  }

  /**
   * Initialize this controller.
   *
   * @return void
   */
  public function initialize ($context)
  {
    $this->context = $context;

    if (sfConfig::get('sf_logging_active'))
    {
      $this->context->getLogger()->info('{sfController} initialization');
    }

    // set max forwards
    $this->maxForwards = sfConfig::get('sf_max_forwards');
  }

  /**
   * Load global filters.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  private function loadGlobalFilters ($filterChain)
  {
    static $list = array();

    // grab our global filter yaml and preset the module name
    $config     = sfConfig::get('sf_app_config_dir').'/filters.yml';
    $moduleName = 'global';

    if (!isset($list[$moduleName]))
    {
      // load global filters
      require_once(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/filters.yml'));
    }

    // register filters
    foreach ($list[$moduleName] as $filter)
    {
      $filterChain->register($filter);
    }
  }

  /**
   * Load module filters.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  private function loadModuleFilters ($filterChain)
  {
    // filter list cache file
    static $list = array();

    // get the module name
    $moduleName = $this->context->getModuleName();

    if (!isset($list[$moduleName]))
    {
      // we haven't loaded a filter list for this module yet
      $config = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/filters.yml';

      if (is_readable($config))
      {
        require_once(sfConfigCache::getInstance()->checkConfig($config));
      }
      else
      {
        // add an emptry array for this module since no filters exist
        $list[$moduleName] = array();
      }
    }

    // register filters
    foreach ($list[$moduleName] as $filter)
    {
      $filterChain->register($filter);
    }
  }

  /**
   * Retrieve a new sfController implementation instance.
   *
   * @param string A sfController implementation name.
   *
   * @return sfController A sfController implementation instance.
   *
   * @throws sfFactoryException If a new controller implementation instance cannot be created.
   */
  public static function newInstance ($class)
  {
    try
    {
      // the class exists
      $object = new $class();

      if (!($object instanceof sfController))
      {
          // the class name is of the wrong type
          $error = 'Class "%s" is not of the type sfController';
          $error = sprintf($error, $class);

          throw new sfFactoryException($error);
      }

      return $object;
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
  }

  /**
   * Set the presentation rendering mode.
   *
   * @param int A rendering mode.
   *
   * @return void
   *
   * @throws sfRenderException - If an invalid render mode has been set.
   */
  public function setRenderMode ($mode)
  {
    if ($mode == sfView::RENDER_CLIENT || $mode == sfView::RENDER_VAR || $mode == sfView::RENDER_NONE)
    {
      $this->renderMode = $mode;

      return;
    }

    // invalid rendering mode type
    $error = 'Invalid rendering mode: %s';
    $error = sprintf($error, $mode);

    throw new sfRenderException($error);
  }

  /**
   * Indicates whether or not we were called using the CLI version of PHP.
   *
   * @return bool true, if using cli, otherwise false.
   */
  public function inCLI()
  {
    return 'cli' == php_sapi_name();
  }
}
