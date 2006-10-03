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
 * sfBasicSecurityUser will handle any type of data as a credential.
 *
 * @package    symfony
 * @subpackage user
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfBasicSecurityUser extends sfUser implements sfSecurityUser
{
  const LAST_REQUEST_NAMESPACE = 'symfony/user/sfUser/lastRequest';
  const AUTH_NAMESPACE = 'symfony/user/sfUser/authenticated';
  const CREDENTIAL_NAMESPACE = 'symfony/user/sfUser/credentials';

  protected $lastRequest = null;

  protected $credentials = null;
  protected $authenticated = null;

  protected $timedout = false;

  /**
   * Clears all credentials.
   *
   */
  public function clearCredentials()
  {
    $this->credentials = null;
    $this->credentials = array();
  }

  /**
   * returns an array containing the credentials
   */
  public function listCredentials()
  {
    return $this->credentials;
  }

  /**
   * Removes a credential.
   *
   * @param  mixed credential
   */  
  public function removeCredential($credential)
  {
    if ($this->hasCredential($credential))
    {
      foreach ($this->credentials as $key => $value)
      {
        if ($credential == $value)
        {
          if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfUser} remove credential "'.$credential.'"');

          unset($this->credentials[$key]);
          return;
        }
      }
    }
  }  

  /**
   * Adds a credential.
   *
   * @param  mixed credential
   */
  public function addCredential($credential)
  {
    $this->addCredentials(func_get_args());
  }

  /**
   * Adds several credential at once.
   *
   * @param  mixed array or list of credentials
   */
  public function addCredentials()
  {
    if (func_num_args() == 0) return;

    // Add all credentials
    $credentials = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfUser} add credential(s) "'.implode(', ', $credentials).'"');

    foreach ($credentials as $aCredential)
    {
      if (!in_array($aCredential, $this->credentials))
      {
        $this->credentials[] = $aCredential;
      }
    }
  }

  /**
   * Returns true if user has credential.
   *
   * @param  mixed credential
   * @return boolean
   */
  public function hasCredential($credential)
  {
    if (is_array($credential))
    {
      return $this->checkCredentials($credential, true);
    }
    else
    {
      return in_array($credential, $this->credentials, true);
    }
  }

  /**
   * Checks an array of credentials
   *
   * @param  array of credentials
   * @param  boolean if user must match all credentials in array
   * @return boolean
   */
  private function checkCredentials($credentials, $matchAll)
  {
    $matched = 0;
    foreach ($credentials as $credential)
    {
      // if credential is an array, then recursive with the opposite logical operator
      $hasCredential = is_array($credential) ? $this->checkCredentials($credential, !$matchAll) : in_array($credential, $this->credentials, true);

      // if the user has the credential, increment number of credential in this array matched
      $matched = $hasCredential ? $matched + 1 : $matched;
    }

    if ($matchAll && count($credentials) == $matched)
    {
      // return true if all credentials must be matched and they have been
      return true;
    }
    else if ($matchAll)
    {
      return false;
    }
    else
    {
      return $matched ? true : false;
    }
  }

  /**
   * Returns true if user is authenticated.
   *
   * @return boolean
   */
  public function isAuthenticated()
  {
    return $this->authenticated;
  }

  /**
   * Sets authentication for user.
   *
   * @param  boolean
   */
  public function setAuthenticated($authenticated)
  {
    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfUser} user is '.($authenticated === true ? '' : 'not ').'authenticated');

    if ($authenticated === true)
    {
      $this->authenticated = true;
    }
    else
    {
      $this->authenticated = false;
      $this->clearCredentials();
    }
  }

  public function setTimedOut()
  {
    $this->timedout = true;
  }

  public function isTimedOut()
  {
    return $this->timedout;
  }

  /**
   * Returns the timestamp of the last user request.
   *
   * @param  integer
   */
  public function getLastRequestTime()
  {
    return $this->lastRequest;
  }

  public function initialize($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context, $parameters);

    // read data from storage
    $storage = $this->getContext()->getStorage();

    $this->authenticated = $storage->read(self::AUTH_NAMESPACE);
    $this->credentials   = $storage->read(self::CREDENTIAL_NAMESPACE);
    $this->lastRequest   = $storage->read(self::LAST_REQUEST_NAMESPACE);

    if ($this->authenticated == null)
    {
      $this->authenticated = false;
      $this->credentials   = array();
    }

    // Automatic logout if no request for more than [sf_timeout]
    if (null !== $this->lastRequest && (time() - $this->lastRequest) > sfConfig::get('sf_timeout'))
    {
      if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfUser} automatic user logout');
      $this->setTimedOut();
      $this->clearCredentials();
      $this->setAuthenticated(false);
    }

    $this->lastRequest = time();
  }

  public function shutdown ()
  {
    $storage = $this->getContext()->getStorage();

    // write the last request time to the storage
    $storage->write(self::LAST_REQUEST_NAMESPACE, $this->lastRequest);

    $storage->write(self::AUTH_NAMESPACE,         $this->authenticated);
    $storage->write(self::CREDENTIAL_NAMESPACE,   $this->credentials);

    // call the parent shutdown method
    parent::shutdown();
  }
}
