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
   * Executes this filter.
   *
   * @param sfFilterChain The filter chain
   *
   * @throws <b>sfInitializeException</b> If an error occurs during view initialization.
   * @throws <b>sfViewException</b>       If an error occurs while executing the view.
   */
  public function execute($filterChain)
  {
    // get the context and controller
    $context    = $this->getContext();
    $controller = $context->getController();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // get the request method
    $method = $context->getRequest()->getMethod();

    $viewName = null;

    // validate and execute the action
    if (sfConfig::get('sf_cache') && null !== $context->getResponse()->getParameter($context->getRouting()->getCurrentInternalUri().'_action', null, 'symfony/cache'))
    {
      // action in cache, so go to the view
      $viewName = sfView::SUCCESS;
    }
    else if (($actionInstance->getRequestMethods() & $method) != $method)
    {
      // this action will skip validation/execution for this method
      // get the default view
      $viewName = $actionInstance->getDefaultView();
    }
    else
    {
      if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
      {
        $timer = sfTimerManager::getTimer(sprintf('Action "%s/%s"', $actionInstance->getModuleName(), $actionInstance->getActionName()));
      }

      $validated = $this->validateAction($actionInstance);

      // register fill-in filter
      if (null !== ($parameters = $context->getRequest()->getAttribute('fillin', null, 'symfony/filter')))
      {
        $this->registerFillInFilter($filterChain, $parameters);
      }

      if (!$validated && sfConfig::get('sf_logging_enabled'))
      {
        $this->context->getLogger()->info('{sfFilter} action validation failed');
      }

      $viewName = $validated ? $this->executeAction($actionInstance) : $this->handleErrorAction($actionInstance);

      if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
      {
        $timer->addTime();
      }
    }

    if (sfView::HEADER_ONLY == $viewName)
    {
      $context->getResponse()->setHeaderOnly(true);

      // execute next filter
      $filterChain->execute();

      return;
    }

    if (sfView::NONE == $viewName)
    {
      return;
    }

    // execute and render the view
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('View "%s" for "%s/%s"', $viewName, $actionInstance->getModuleName(), $actionInstance->getActionName()));
    }

    $viewData = $this->executeView($actionInstance->getModuleName(), $actionInstance->getActionName(), $viewName);

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    if (sfView::RENDER_VAR == $controller->getRenderMode())
    {
      $actionEntry->setPresentation($viewData);

      return;
    }

    // execute next filter
    $filterChain->execute();
  }

  protected function validateAction($actionInstance)
  {
    $moduleName = $actionInstance->getModuleName();
    $actionName = $actionInstance->getActionName();

    // set default validated status
    $validated = true;

    // get the current action validation configuration
    $context = $this->getContext();
    $validationConfig = $moduleName.'/'.sfConfig::get('sf_app_module_validate_dir_name').'/'.$actionName.'.yml';

    // load validation configuration
    // do NOT use require_once
    if (null !== $validateFile = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$validationConfig, true))
    {
      // create validator manager
      $validatorManager = new sfValidatorManager();
      $validatorManager->initialize($this->getContext());

      require($validateFile);

      // process validators
      $validated = $validatorManager->execute();
    }

    // process manual validation
    $validateToRun = 'validate'.ucfirst($actionName);
    $manualValidated = method_exists($actionInstance, $validateToRun) ? $actionInstance->$validateToRun() : $actionInstance->validate();

    // action is validated if:
    // - all validation methods (manual and automatic) return true
    // - or automatic validation returns false but errors have been 'removed' by manual validation
    return ($manualValidated && $validated) || ($manualValidated && !$validated && !$context->getRequest()->hasErrors());
  }

  /**
   * Executes the execute method of an action.
   *
   * @param sfAction An sfAction instance
   *
   * @param string   The view type
   */
  protected function executeAction($actionInstance)
  {
    // execute the action
    $actionInstance->preExecute();
    $viewName = $actionInstance->execute();
    $actionInstance->postExecute();

    return $viewName ? $viewName : sfView::SUCCESS;
  }

  /**
   * Executes the handleError method of an action.
   *
   * @param sfAction An sfAction instance
   *
   * @param string   The view type
   */
  protected function handleErrorAction($actionInstance)
  {
    // validation failed
    $handleErrorToRun = 'handleError'.ucfirst($actionInstance->getActionName());
    $viewName = method_exists($actionInstance, $handleErrorToRun) ? $actionInstance->$handleErrorToRun() : $actionInstance->handleError();

    return $viewName ? $viewName : sfView::ERROR;
  }

  /**
   * Executes and renders the view.
   *
   * @param string The module name
   * @param string The action name
   * @param string The view name
   *
   * @param string The view data
   */
  protected function executeView($moduleName, $actionName, $viewName)
  {
    // get the view instance
    $viewInstance = $this->getContext()->getController()->getView($moduleName, $actionName, $viewName);
    $viewInstance->initialize($this->getContext(), $moduleName, $actionName, $viewName);

    $viewInstance->execute();

    // render the view and if data is returned, stick it in the
    // action entry which was retrieved from the execution chain
    $viewData = $viewInstance->render();

    return $viewData;
  }

  /**
   * Registers the fill in filter in the filter chain.
   *
   * @param sfFilterChain A sfFilterChain implementation instance
   * @param array An array of parameters to pass to the fill in filter.
   */
  protected function registerFillInFilter($filterChain, $parameters)
  {
    // automatically register the fill in filter if it is not already loaded in the chain
    if (isset($parameters['enabled']) && $parameters['enabled'] && !$filterChain->hasFilter('sfFillInFormFilter'))
    {
      // register the fill in form filter
      $fillInFormFilter = new sfFillInFormFilter();
      $fillInFormFilter->initialize($this->context, isset($parameters['param']) ? $parameters['param'] : array());
      $filterChain->register($fillInFormFilter);
    }
  }
}
