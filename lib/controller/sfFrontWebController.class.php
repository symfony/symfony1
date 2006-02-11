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
 * @version    SVN: $Id$
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
      if (sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfFrontWebController} dispatch request');
      }

      // determine our module and action
      $request    = $this->getContext()->getRequest();
      $moduleName = $request->getParameter('module');
      $actionName = $request->getParameter('action');

      // make the first request
      $this->forward($moduleName, $actionName);
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      // unknown exception
      $e = new sfException(get_class($e).': '.$e->getMessage());

      $e->printStackTrace();
    }
  }
}

?>