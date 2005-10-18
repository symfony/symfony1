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
 * sfSecurityUser interface provides advanced security manipulation methods.
 *
 * @package    symfony
 * @subpackage user
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
interface sfSecurityUser
{
  /**
   * Add a credential to this user.
   *
   * @param mixed Credential data.
   *
   * @return void
   */
  public function addCredential ($credential);

  /**
   * Clear all credentials associated with this user.
   *
   * @return void
   */
  public function clearCredentials ();

  /**
   * Indicates whether or not this user has a credential.
   *
   * @param mixed Credential data.
   *
   * @return bool true, if this user has the credential, otherwise false.
   */
  public function hasCredential ($credential);

  /**
   * Indicates whether or not this user is authenticated.
   *
   * @return bool true, if this user is authenticated, otherwise false.
   */
  public function isAuthenticated ();

  /**
   * Remove a credential from this user.
   *
   * @param mixed Credential data.
   *
   * @return void
   */
  public function removeCredential ($credential);

  /**
   * Set the authenticated status of this user.
   *
   * @param bool A flag indicating the authenticated status of this user.
   *
   * @return void
   */
  public function setAuthenticated ($authenticated);
}

?>