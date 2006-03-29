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
 * sfAction executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfActions extends sfAction
{
  /**
   * Execute any application/business logic for this action.
   *
   * In a typical database-driven application, execute() handles application
   * logic itself and then proceeds to create a model instance. Once the model
   * instance is initialized it handles all business logic for the action.
   *
   * A model should represent an entity in your application. This could be a
   * user account, a shopping cart, or even a something as simple as a
   * single product.
   *
   * @return mixed A string containing the view name associated with this action.
   *
   *               Or an array with the following indices:
   *
   *               - The parent module of the view that will be executed.
   *               - The view that will be executed.
   */
  public function execute()
  {
    // dispatch action
    $method = strtolower($this->getRequest()->getRequestMethod());
    $action = ucfirst($this->getActionName());

    if (method_exists($this, $method.$action))
    {
      $actionToRun = $method.$action;
    }
    else if (method_exists($this, 'execute'.$action))
    {
      // DEPRECATED
      $actionToRun = 'execute'.$action;
      if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->err(sprintf('The usage of \'execute\' as a prefix for actions is deprecated ("%s"). Please prefix your actions with the request method ("%s")', $actionToRun, $method.$action));
    }
    else
    {
      // action not found
      $error = 'sfAction initialization failed for module "%s", action "%s". You must create a "%s" method.';
      $error = sprintf($error, $this->getModuleName(), $this->getActionName(), $method.$action);
      throw new sfInitializationException($error);
    }

    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfActions} call "'.get_class($this).'->'.$actionToRun.'()'.'"');

    // run action
    $ret = $this->$actionToRun();

    return $ret;
  }
}

?>