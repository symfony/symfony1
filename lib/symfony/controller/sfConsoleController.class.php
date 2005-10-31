<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */
class sfConsoleController extends sfController
{
  /**
   * Dispatch a request.
   *
   * @param string A module name.
   * @param string An action name.
   * @param array  An associative array of parameters to be set.
   *
   * @return void
   */
  public function dispatch ($moduleName, $actionName, $parameters = array())
  {
    try
    {
      // initialize the controller
      $this->initialize();

      // set parameters
      $this->getContext()->getRequest()->getParameterHolder()->addByRef($parameters);

      // make the first request
      $this->forward($moduleName, $actionName);
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      // most likely an exception from a third-party library
      $e = new AgaviException($e->getMessage());

      $e->printStackTrace();
    }
  }
}

?>