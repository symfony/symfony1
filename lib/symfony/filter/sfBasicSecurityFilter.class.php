<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBasicSecurityFilter checks security by calling the getCredential() method
 * of the action. Once the credential has been acquired, BasicSecurityFilter
 * verifies the user has the same credential by calling the hasCredential()
 * method of SecurityUser.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfBasicSecurityFilter extends sfSecurityFilter
{
  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute ($filterChain)
  {
    // get the cool stuff
    $context    = $this->getContext();
    $controller = $context->getController();
    $user       = $context->getUser();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // get the credential required for this action
    $credential = $actionInstance->getCredential();

    // for this filter, the credentials are a simple privilege array
    // where the first index is the privilege name and the second index
    // is the privilege namespace
    //
    // NOTE: the nice thing about the Action class is that getCredential()
    //       is vague enough to describe any level of security and can be
    //       used to retrieve such data and should never have to be altered
    if ($user->isAuthenticated())
    {
      // the user is authenticated
      if ($credential === null || $user->hasCredential($credential))
      {
        // the user has access, continue
        $filterChain->execute();
      }
      else
      {
        // the user doesn't have access, exit stage left
        $controller->forward(SF_SECURE_MODULE, SF_SECURE_ACTION);
      }
    }
    else
    {
      // the user is not authenticated
      $controller->forward(SF_LOGIN_MODULE, SF_LOGIN_ACTION);
    }
  }
}

?>