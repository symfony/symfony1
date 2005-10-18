<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfFrontWebController.class.php 495 2005-10-05 15:42:11Z fabien $
 */

/**
 * sfFrontWebController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
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

      if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfFrontWebController} dispatch request');

      // determine our module and action
      $moduleName = $context->getRequest()->getParameter(SF_MODULE_ACCESSOR);
      $actionName = $context->getRequest()->getParameter(SF_ACTION_ACCESSOR);

      if ($moduleName == null)
      {
        $moduleName = SF_DEFAULT_MODULE;

        if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfFrontWebController} no module, set default ('.$moduleName.')');
      }

      if ($actionName == null)
      {
        // no action has been specified
        $actionName = SF_DEFAULT_ACTION;

        if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfFrontWebController} no action, set default ('.$actionName.')');
      }

      // make the first request
      $this->forward($moduleName, $actionName);

      // send web debug information if needed
      if (SF_WEB_DEBUG)
      {
        sfWebDebug::printResults();
      }

    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
  }
}

?>