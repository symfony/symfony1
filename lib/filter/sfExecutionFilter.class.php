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
 * sfValidationExecutionFilter is the last filter registered for each filter chain. This
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
    // get the current action instance
    $actionInstance = $this->context->getController()->getActionStack()->getLastEntry()->getActionInstance();

    // execute the action, execute and render the view
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Action "%s/%s"', $actionInstance->getModuleName(), $actionInstance->getActionName()));

      $viewName = $this->handleAction($filterChain, $actionInstance);

      $timer->addTime();
      $timer = sfTimerManager::getTimer(sprintf('View "%s" for "%s/%s"', $viewName, $actionInstance->getModuleName(), $actionInstance->getActionName()));

      $this->handleView($filterChain, $actionInstance, $viewName);

      $timer->addTime();
    }
    else
    {
      $viewName = $this->handleAction($filterChain, $actionInstance);
      $this->handleView($filterChain, $actionInstance, $viewName);
    }
  }

  /*
   * Handles the action.
   *
   * @param  sfFilterChain The current filter chain
   * @param  sfAction      An sfAction instance
   *
   * @return string        The view type
   */
  protected function handleAction($filterChain, $actionInstance)
  {
    if (sfConfig::get('sf_cache') && $this->context->getViewCacheManager()->hasActionCache($this->context->getRouting()->getCurrentInternalUri()))
    {
      // action in cache, so go to the view
      return sfView::SUCCESS;
    }

    return $this->executeAction($actionInstance);
  }

  /**
   * Executes the execute method of an action.
   *
   * @param  sfAction An sfAction instance
   *
   * @return string   The view type
   */
  protected function executeAction($actionInstance)
  {
    // execute the action
    $actionInstance->preExecute();
    $viewName = $actionInstance->execute($this->context->getRequest());
    $actionInstance->postExecute();

    return is_null($viewName) ? sfView::SUCCESS : $viewName;
  }

  /**
   * Handles the view.
   *
   * @param  sfFilterChain The current filter chain
   * @param sfAction       An sfAction instance
   * @param string         The view name
   */
  protected function handleView($filterChain, $actionInstance, $viewName)
  {
    switch ($viewName)
    {
      case sfView::HEADER_ONLY:
        $this->context->getResponse()->setHeaderOnly(true);
        return;
      case sfView::NONE:
        return;
    }

    $this->executeView($actionInstance->getModuleName(), $actionInstance->getActionName(), $viewName, $actionInstance->getVarHolder()->getAll());
  }

  /**
   * Executes and renders the view.
   *
   * The behavior of this method depends on the controller render mode:
   *
   *   - sfView::NONE: Nothing happens.
   *   - sfView::RENDER_CLIENT: View data populates the response content.
   *   - sfView::RENDER_DATA: View data populates the data presentation variable.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The view name
   * @param  array  An array of view attributes
   *
   * @return string The view data
   */
  protected function executeView($moduleName, $actionName, $viewName, $viewAttributes)
  {
    $controller = $this->context->getController();

    // get the view instance
    $view = $controller->getView($moduleName, $actionName, $viewName);

    // execute the view
    $view->execute();

    // pass attributes to the view
    $view->getAttributeHolder()->add($viewAttributes);

    // render the view
    switch ($controller->getRenderMode())
    {
      case sfView::RENDER_NONE:
        break;

      case sfView::RENDER_CLIENT:
        $viewData = $view->render();
        $this->context->getResponse()->setContent($viewData);
        break;

      case sfView::RENDER_VAR:
        $viewData = $view->render();
        $controller->getActionStack()->getLastEntry()->setPresentation($viewData);
        break;
    }
  }
}
