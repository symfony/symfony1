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
 * sfExecutionFilter is the last filter registered for each filter chain. This
 * filter does all action and view execution.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfExecutionFilter extends sfFilter
{
  /**
   * Execute this filter.
   *
   * @param sfFilterChain The filter chain.
   *
   * @return void
   *
   * @throws <b>sfInitializeException</b> If an error occurs during view initialization.
   * @throws <b>sfViewException</b>       If an error occurs while executing the view.
   */
  public function execute ($filterChain)
  {
    // get the context and controller
    $context    = $this->getContext();
    $controller = $context->getController();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // get the current action information
    $moduleName = $context->getModuleName();
    $actionName = $context->getActionName();

    // get the request method
    $method = $context->getRequest()->getMethod();

    $viewName = null;

    if (sfConfig::get('sf_cache') && !sfConfig::get('sf_cache_always_execute_action', false))
    {
      list($uri, $suffix) = $context->getViewCacheManager()->getInternalUri('slot');
      if ($context->getResponse()->getParameter($uri.'_'.$suffix, null, 'symfony/cache') !== null)
      {
        // action in cache, so go to the view
        $viewName = sfView::RENDER_CACHE;
      }
    }

    if (!$viewName)
    {
      // create validator manager
      $validatorManager = new sfValidatorManager();
      $validatorManager->initialize($context);

      if (($actionInstance->getRequestMethods() & $method) != $method)
      {
        // this action will skip validation/execution for this method
        // get the default view
        $viewName = $actionInstance->getDefaultView();
      }
      else
      {
        // set default validated status
        $validated = true;

        // get the current action validation configuration
        $validationConfig = $moduleName.'/'.sfConfig::get('sf_app_module_validate_dir_name').'/'.$actionName.'.yml';

        // load validation configuration
        // do NOT use require_once
        if (null !== $validateFile = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$validationConfig, true))
        {
          require($validateFile);
        }

        // manually load validators
        $actionInstance->registerValidators($validatorManager);

        // process validators
        $validated = $validatorManager->execute();

        // process manual validation
        $validateToRun = 'validate'.ucfirst($actionName);
        $manualValidated = method_exists($actionInstance, $validateToRun) ? $actionInstance->$validateToRun() : $actionInstance->validate();

        // action is validated if:
        // - all validation methods (manual and automatic) return true
        // - or automatic validation returns false but errors have been 'removed' by manual validation
        $validated = ($manualValidated && $validated) || ($manualValidated && !$validated && !$context->getRequest()->hasErrors());

        $sf_logging_active = sfConfig::get('sf_logging_active');
        if ($validated)
        {
          if ($sf_logging_active)
          {
            $timer = sfTimerManager::getTimer(sprintf('Action "%s/%s"', $moduleName, $actionName));
          }

          // execute the action
          $actionInstance->preExecute();
          $viewName = $actionInstance->execute();
          if ($viewName == '')
          {
            $viewName = sfView::SUCCESS;
          }
          $actionInstance->postExecute();

          if ($sf_logging_active)
          {
            $timer->addTime();
          }
        }
        else
        {
          if ($sf_logging_active)
          {
            $this->context->getLogger()->info('{sfExecutionFilter} action validation failed');
          }

          // validation failed
          $handleErrorToRun = 'handleError'.ucfirst($actionName);
          $viewName = method_exists($actionInstance, $handleErrorToRun) ? $actionInstance->$handleErrorToRun() : $actionInstance->handleError();
        }

        // register fill-in filter
        if (null !== ($parameters = $context->getRequest()->getAttribute('fillin', null, 'symfony/filter')))
        {
          $this->registerFillInFilter($filterChain, $parameters);
        }
      }
    }

    if ($viewName == sfView::HEADER_ONLY)
    {
      $filterChain->executionFilterDone();

      // execute next filter
      $filterChain->execute();
    }
    else if ($viewName != sfView::NONE)
    {
      if (sfConfig::get('sf_logging_active'))
      {
        $timer = sfTimerManager::getTimer(sprintf('View "%s" for "%s/%s"', $viewName, $moduleName, $actionName));
      }

      // get the view instance
      $viewInstance = $controller->getView($moduleName, $actionName, $viewName);

      // initialize the view
      if ($viewInstance->initialize($context, $moduleName, $actionName, $viewName))
      {
        // view initialization completed successfully
        $viewInstance->execute();

        // render the view and if data is returned, stick it in the
        // action entry which was retrieved from the execution chain
        $viewData = $viewInstance->render();

        if (sfConfig::get('sf_logging_active'))
        {
          $timer->addTime();
        }

        if ($controller->getRenderMode() == sfView::RENDER_VAR)
        {
          $actionEntry->setPresentation($viewData);
        }
        else
        {
          $filterChain->executionFilterDone();

          // execute next filter
          $filterChain->execute();
        }
      }
      else
      {
        // view failed to initialize
        $error = 'View initialization failed for module "%s", view "%sView"';
        $error = sprintf($error, $moduleName, $viewName);

        throw new sfInitializationException($error);
      }
    }
  }

  private function registerFillInFilter($filterChain, $parameters)
  {
    // automatically register the fill in filter if it is not already loaded in the chain
    if (isset($parameters['activate']) && $parameters['activate'] && !$filterChain->hasFilter('sfFillInFormFilter'))
    {
      // register the fill in form filter
      $fillInFormFilter = new sfFillInFormFilter();
      $fillInFormFilter->initialize($this->context, isset($parameters['param']) ? $parameters['param'] : array());
      $filterChain->register($fillInFormFilter);
    }
  }
}
