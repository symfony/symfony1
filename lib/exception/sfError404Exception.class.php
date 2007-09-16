<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfError404Exception is thrown when a 404 error occurs in an action.
 *
 * @package    symfony
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfError404Exception extends sfException
{
  /**
   * Forwards to the 404 action.
   */
  public function printStackTrace()
  {
    if (sfConfig::get('sf_debug'))
    {
      sfContext::getInstance()->getResponse()->setStatusCode(404);

      return parent::printStackTrace();
    }
    else
    {
      sfContext::getInstance()->getController()->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
    }
  }
}
