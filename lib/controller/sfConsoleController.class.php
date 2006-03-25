<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
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
      // use plain text for errors
      sfException::setFormat('plain');

      // set parameters
      $this->getContext()->getRequest()->getParameterHolder()->add($parameters);

      // make the first request
      $this->forward($moduleName, $actionName);
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
    catch (Exception $e)
    {
      // wrap non symfony exceptions
      $sfException = new sfException();
      $sfException->printStackTrace($e);
    }
  }
}

?>