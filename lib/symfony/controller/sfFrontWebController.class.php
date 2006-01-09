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
 * sfFrontWebController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfFrontWebController.class.php 495 2005-10-05 15:42:11Z fabien $
 */
class sfFrontWebController extends sfWebController
{
  /**
   * Dispatch a request.
   *
   * This will determine which module and action to use by request parameters
   * specified by the user.
   *
   * @return void
   */
  public function dispatch ()
  {
    try
    {
      // get the application context
      $context = $this->getContext();

      if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfFrontWebController} dispatch request');

      // determine our module and action
      $moduleName = $context->getRequest()->getParameter(sfConfig::get('sf_module_accessor'));
      $actionName = $context->getRequest()->getParameter(sfConfig::get('sf_action_accessor'));

      if ($moduleName == null)
      {
        $moduleName = sfConfig::get('sf_default_module');

        if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfFrontWebController} no module, set default ('.$moduleName.')');
      }

      if ($actionName == null)
      {
        // no action has been specified
        $actionName = sfConfig::get('sf_default_action');

        if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfFrontWebController} no action, set default ('.$actionName.')');
      }

      // register sfWebDebug assets
      if (sfConfig::get('sf_web_debug'))
      {
        sfWebDebug::getInstance()->registerAssets();
      }

      // make the first request
      $this->forward($moduleName, $actionName);

      // send web debug information if needed
      if (sfConfig::get('sf_web_debug'))
      {
        sfWebDebug::getInstance()->printResults();
      }
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      // unknown exception
      $e = new sfException($e->getMessage());

      $e->printStackTrace();
    }
  }
}

?>