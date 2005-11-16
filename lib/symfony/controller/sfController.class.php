<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
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
    $maxForwards              = 20,
    $renderMode               = sfView::RENDER_CLIENT,
    $executionFilterClassName = null,
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
   * Indicates whether or not a module has a specific action.
   *
   * @param string A module name.
   * @param string An action name.
   *
   * @return bool true, if the action exists, otherwise false.
   */
  public function actionExists ($moduleName, $actionName)
  {
    $file = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_ACTION_DIR_NAME.'/'.$actionName.'Action.class.php';
    $module_file = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_ACTION_DIR_NAME.'/actions.class.php';
    $core_module_file = SF_SYMFONY_DATA_DIR.'/symfony/modules/'.$moduleName.'/actions/actions.class.php';
    $constant_name = 'SF_MODULE_'.strtoupper($moduleName);

    $exists = false;

    if (is_readable($file))
    {
      // action class exists
      $exists = true;

      require_once($file);
    }
    else if (is_readable($module_file))
    {
      // module class exists
      require_once($module_file);

      // action is defined in this class?
      if (is_callable(array($moduleName.'Actions', 'execute'.$actionName)))
      {
        $exists = true;
      }
    }
    else if (defined($constant_name) && constant($constant_name) && is_readable($core_module_file))
    {
      // core module file exists and is active (see settings.yml)
      $exists = true;

      require_once($core_module_file);
    }

    return $exists;
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
      $error = 'Too many forwards have been detected for this request';
      throw new sfForwardException($error);
    }

    if (!SF_AVAILABLE)
    {
      // application is unavailable
      $moduleName = SF_UNAVAILABLE_MODULE;
      $actionName = SF_UNAVAILABLE_ACTION;

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find unavailable module/action
        $error = 'Invalid configuration settings: SF_UNAVAILABLE_MODULE "%s", SF_UNAVAILABLE_ACTION "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfConfigurationException($error);
      }
    }

    // check for a module generator config file
    $generatorConfig = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/generator.yml';
    if (is_readable($generatorConfig))
    {
      sfConfigCache::import($generatorConfig, true, array('moduleName' => $moduleName));
    }

    if (!$this->actionExists($moduleName, $actionName))
    {
      // the requested action doesn't exist
      if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfController} action does not exist');

      // track the requested module so we have access to the data in the error 404 page
      $this->context->getRequest()->setAttribute('requested_action', $actionName);
      $this->context->getRequest()->setAttribute('requested_module', $moduleName);
      $this->context->getRequest()->setAttribute('requested_uri',    $_SERVER['PHP_SELF']);

      // switch to error 404 action
      $moduleName = SF_ERROR_404_MODULE;
      $actionName = SF_ERROR_404_ACTION;

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find unavailable module/action
        $error = 'Invalid configuration settings: SF_ERROR_404_MODULE "%s", SF_ERROR_404_ACTION "%s"';
        $error = sprintf($error, $moduleName, $actionName);

        throw new sfConfigurationException($error);
      }
    }

    // create an instance of the action
    $actionInstance = $this->getAction($moduleName, $actionName);

    // add a new action stack entry
    $this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance, $isSlot);

    // include module configuration
    sfConfigCache::import('modules/'.$moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/module.yml', true, array('prefix' => $moduleName.'_'));

    if (constant('MOD_'.strtoupper($moduleName).'_ENABLED'))
    {
      // module is enabled

      // check for a module config.php
      $moduleConfig = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/config.php';
      if (is_readable($moduleConfig))
      {
        require_once($moduleConfig);
      }

      // initialize the action
      if ($actionInstance->initialize($this->context))
      {
        // create a new filter chain
        $filterChain = new sfFilterChain();

        if (SF_AVAILABLE)
        {
          // the application is available so we'll register
          // global and module filters, otherwise skip them

          // does this action require security?
          if (SF_USE_SECURITY && $actionInstance->isSecure())
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

        // register the execution filter
        $execFilter = new $this->executionFilterClassName();

        $execFilter->initialize($this->context);
        $filterChain->register($execFilter);

        if ($moduleName == SF_ERROR_404_MODULE && $actionName == SF_ERROR_404_ACTION && !headers_sent())
        {
          header('HTTP/1.0 404 Not Found');
          header('Status: 404 Not Found');
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
      $moduleName = SF_MODULE_DISABLED_MODULE;
      $actionName = SF_MODULE_DISABLED_ACTION;

      if (!$this->actionExists($moduleName, $actionName))
      {
        // cannot find mod disabled module/action
        $error = 'Invalid configuration settings: SF_MODULE_DISABLED_MODULE "%s", SF_MODULE_DISABLED_ACTION "%s"';
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
    $file = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_ACTION_DIR_NAME.'/'.$actionName.'Action.class.php';

    if (is_readable($file))
    {
      $class = $actionName.'Action';

      // fix for same name classes
      $moduleClass = $moduleName.'_'.$class;

      if (class_exists($moduleClass, false))
      {
        $class = $moduleClass;
      }
    }
    else
    {
      $class = $moduleName.'Actions';
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
   * @param string A view name.
   *
   * @return View A View implementation instance, if the view exists, otherwise null.
   */
  public function getView ($moduleName, $viewName)
  {
    // user view exists?
    $file = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_VIEW_DIR_NAME.'/'.$viewName.'View.class.php';

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

      return new $class();
    }

    // default view
    return new sfRenderView($viewName);
  }

  /**
   * Indicates whether or not a module has a specific view.
   *
   * @param string A module name.
   * @param string A view name.
   *
   * @return bool true, if the view exists, otherwise false.
   */
  public function viewExists ($moduleName, $viewName)
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
   * Initialize this controller.
   *
   * @return void
   */
  public function initialize ($context)
  {
    $this->context = $context;

    if (SF_LOGGING_ACTIVE) $this->context->getLogger()->info('{sfController} initialization');

    // set max forwards
    $this->maxForwards = SF_MAX_FORWARDS;
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

    // grab our global filter ini and preset the module name
    $config     = SF_APP_CONFIG_DIR.'/filters.yml';
    $moduleName = 'global';

    if (!isset($list[$moduleName]) && is_readable($config))
    {
      // load global filters
      require_once(sfConfigCache::checkConfig(SF_APP_CONFIG_DIR_NAME.'/filters.yml'));
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
      $config = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/filters.yml';

      if (is_readable($config))
        require_once(sfConfigCache::checkConfig($config));
      else
      {
        // add an emptry array for this module since no filters exist
        $list[$moduleName] = array();
      }
    }

    // register filters
    foreach ($list[$moduleName] as $filter)
      $filterChain->register($filter);
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

}

?>