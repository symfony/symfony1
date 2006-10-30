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
  protected
    $context                  = null,
    $controllerClasses        = array(),
    $maxForwards              = 5,
    $renderMode               = sfView::RENDER_CLIENT,
    $viewCacheClassName       = null;

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
    return $this->controllerExists($moduleName, $componentName, 'component', false);
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
    return $this->controllerExists($moduleName, $actionName, 'action', false);
  }

  /**
   * Look for a controller and optionally throw exceptions if existence is required (i.e.
   * in the case of {@link getController()}).
   *
   * @param string $moduleName the name of the module
   * @param string $controllerName the name of the controller within the module
   * @param string $extension either 'action' or 'component' depending on the type of
   *               controller to look for
   * @param boolean $throwExceptions whether to throw exceptions if the controller doesn't exist
   *
   * @throws sfConfigurationException thrown if the module is not activated
   * @throws sfControllerException thrown if the controller doesn't exist and the $throwExceptions
   *                               parameter is set to true
   *
   * @return boolean true if the controller exists; false otherwise
   */
  protected function controllerExists ($moduleName, $controllerName, $extension, $throwExceptions)
  {
    $dirs = sfLoader::getControllerDirs($moduleName);
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

        if (!class_exists($moduleName.$classSuffix.'s', false))
        {
          if ($throwExceptions)
          {
            throw new sfControllerException(sprintf('There is no "%s" class in your action file "%s".', $moduleName.$classSuffix.'s', $module_file));
          }

          return false;
        }

        // action is defined in this class?
        if (!method_exists($moduleName.'Actions', '__call') && !in_array('execute'.ucfirst($controllerName), get_class_methods($moduleName.$classSuffix.'s')))
        {
          if ($throwExceptions)
          {
            throw new sfControllerException(sprintf('There is no "%s" method in your action class "%s"', 'execute'.ucfirst($controllerName), $moduleName.$classSuffix.'s'));
          }

          return false;
        }

        $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $moduleName.$classSuffix.'s';
        return true;
      }
    }

    // send an exception if debug
    if ($throwExceptions && sfConfig::get('sf_debug'))
    {
      $dirs = array_keys($dirs);

      // remove sf_root_dir from dirs
      foreach ($dirs as &$dir)
      {
        $dir = str_replace(sfConfig::get('sf_root_dir'), '%SF_ROOT_DIR%', $dir);
      }

      throw new sfControllerException(sprintf('{sfController} controller "%s/%s" does not exist in: %s', $moduleName, $controllerName, implode(', ', $dirs)));
    }

    return false;
  }

  /**
   * Forward the request to another action.
   *
   * @param string  A module name.
   * @param string  An action name.
   *
   * @return void
   *
   * @throws <b>sfConfigurationException</b> If an invalid configuration setting has been found.
   * @throws <b>sfForwardException</b> If an error occurs while forwarding the request.
   * @throws <b>sfInitializationException</b> If the action could not be initialized.
   * @throws <b>sfSecurityException</b> If the action requires security but the user implementation is not of type sfSecurityUser.
   */
  public function forward ($moduleName, $actionName)
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

    $rootDir = sfConfig::get('sf_root_dir');
    $app     = sfConfig::get('sf_app');
    $env     = sfConfig::get('sf_environment');

    if (!sfConfig::get('sf_available') || sfToolkit::hasLockFile($rootDir.'/'.$app.'_'.$env.'.clilock'))
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
    $this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance);

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
        $this->loadFilters($filterChain, $actionInstance);

        if ($moduleName == sfConfig::get('sf_error_404_module') && $actionName == sfConfig::get('sf_error_404_action'))
        {
          $this->getContext()->getResponse()->setStatusCode(404);
          $this->getContext()->getResponse()->setHttpHeader('Status', '404 Not Found');

          foreach (sfMixer::getCallables('sfController:forward:error404') as $callable)
          {
            call_user_func($callable, $this, $moduleName, $actionName);
          }
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

  protected function getController ($moduleName, $controllerName, $extension)
  {
    $classSuffix = ucfirst(strtolower($extension));
    if (!isset($this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix]))
    {
      $this->controllerExists($moduleName, $controllerName, $extension, true);
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
    $file = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_view_dir_name').'/'.$actionName.$viewName.'View.class.php';

    if (is_readable($file))
    {
      require_once($file);

      $class = $actionName.$viewName.'View';

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
      $viewName = $this->getContext()->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', sfConfig::get('mod_'.strtolower($moduleName).'_view_class'), 'symfony/action/view');
      $class    = sfCore::getClassPath($viewName.'View') ? $viewName.'View' : 'sfPHPView';
    }

    return new $class();
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

  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfController:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfController::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }

  /**
   * Load module filters.
   *
   * @param sfFilterChain A sfFilterChain instance.
   * @param sfAction      A sfAction instance.
   *
   * @return void
   */
  public function loadFilters($filterChain, $actionInstance)
  {
    // register the rendering filter
    list($renderingFilterClassName, $renderingFilterParameters) = (array) sfConfig::get('sf_factory_rendering_filter');
    if (!class_exists($renderingFilterClassName))
    {
      throw new sfConfigurationException(sprintf('Rendering filter class "%s" does not exists', $renderingFilterClassName));
    }
    $renderFilter = new $renderingFilterClassName();
    $renderFilter->initialize($this->context, $renderingFilterParameters);
    $filterChain->register($renderFilter);

    if (sfConfig::get('sf_web_debug'))
    {
      // register web debug toolbar filter
      $webDebugFilter = new sfWebDebugFilter();
      $webDebugFilter->initialize($this->context);
      $filterChain->register($webDebugFilter);
    }

    if (sfConfig::get('sf_available'))
    {
      // the application is available so we'll register
      // global and module filters, otherwise skip them

      // does this action require security?
      if (sfConfig::get('sf_use_security') && $actionInstance->isSecure())
      {
        if (!in_array('sfSecurityUser', class_implements($this->context->getUser())))
        {
          $error = 'Security is enabled, but your sfUser implementation does not implement sfSecurityUser interface';
          throw new sfSecurityException($error);
        }

        // register security filter
        list($securityFilterClassName, $securityFilterParameters) = (array) sfConfig::get('sf_factory_security_filter');
        $securityFilter = sfSecurityFilter::newInstance($securityFilterClassName);
        $securityFilter->initialize($this->context, $securityFilterParameters);
        $filterChain->register($securityFilter);
      }

      $moduleName = $this->context->getModuleName();

      // register module/global filters
      require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/filters.yml'));
    }

    if (sfConfig::get('sf_cache'))
    {
      // register cache filter
      $cacheFilter = new sfCacheFilter();
      $cacheFilter->initialize($this->context);
      $filterChain->register($cacheFilter);
    }

    // register common HTTP filter
    $commonFilter = new sfCommonFilter();
    $commonFilter->initialize($this->context);
    $filterChain->register($commonFilter);

    if (sfConfig::get('sf_use_flash'))
    {
      // register flash filter
      $flashFilter = new sfFlashFilter();
      $flashFilter->initialize($this->context);
      $filterChain->register($flashFilter);
    }

    // register the execution filter
    list($executionFilterClassName, $executionFilterParameters) = (array) sfConfig::get('sf_factory_execution_filter');
    if (!class_exists($executionFilterClassName))
    {
      throw new sfConfigurationException(sprintf('Execution filter class "%s" does not exists', $executionFilterClassName));
    }
    $execFilter = new $executionFilterClassName();
    $execFilter->initialize($this->context, $executionFilterParameters);
    $filterChain->register($execFilter);
  }
}
