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
  public function dispatch()
  {
    try
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getLogger()->info('{sfController} dispatch request');
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
      if (sfConfig::get('sf_test'))
      {
        throw $e;
      }

      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      if (sfConfig::get('sf_test'))
      {
        throw $e;
      }

      try
      {
        // wrap non symfony exceptions
        $sfException = new sfException();
        $sfException->printStackTrace($e);
      }
      catch (Exception $e)
      {
        header('HTTP/1.0 500 Internal Server Error');
      }
    }
  }
}
