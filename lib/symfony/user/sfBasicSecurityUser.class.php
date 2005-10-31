<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
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
  const CULTURE_NAMESPACE = 'symfony/user/sfUser/culture';
  const LAST_REQUEST_NAMESPACE = 'symfony/user/sfUser/lastRequest';
  const AUTH_NAMESPACE = 'symfony/user/sfUser/authenticated';
  const CREDENTIAL_NAMESPACE = 'symfony/user/sfUser/credentials';

  private $culture = null;
  private $lastRequest = null;

  private $credentials = null;
  private $authenticated = null;

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
   * Removes a credential.
   *
   * @param  mixed credential
   */
  public function removeCredential($credential)
  {
    if ($this->hasCredential($credential))
    {
      for ($i = 0, $z = count($this->credentials); $i < $z; $i++)
      {
        if ($credential == $this->credentials[$i])
        {
          if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfUser} remove credential "'.$credential.'"');

          unset($this->credentials[$i]);
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

    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfUser} add credential(s) "'.implode(', ', $credentials).'"');

    foreach ($credentials as $aCredential)
      if (!in_array($aCredential, $this->credentials)) $this->credentials[] = $aCredential;
  }

  /**
   * Returns true if user has credential.
   *
   * @param  mixed credential
   * @return boolean
   */
  public function hasCredential($credential)
  {
    if (func_num_args() == 0) return false;

    // Get all credentials
    $credentials = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

    foreach ($credentials as $aCredential)
      if (in_array($aCredential, $this->credentials)) return true;

    return false;
  }

  /**
   * Sets culture.
   *
   * @param  string culture
   */
  public function setCulture ($culture)
  {
    $this->culture = $culture;
  }

  /**
   * Gets culture.
   *
   * @return string
   */
  public function getCulture()
  {
    return $this->culture;
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
    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfUser} user is '.($authenticated === true ? '' : 'not').' authenticated');

    if ($authenticated === true)
      $this->authenticated = true;
    else
    {
      $this->authenticated = false;
      $this->clearCredentials();
    }
  }

  public function initialize($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context, $parameters);

    // read data from storage
    $storage = $this->getContext()->getStorage();

    $this->authenticated = $storage->read(self::AUTH_NAMESPACE);
    $this->credentials   = $storage->read(self::CREDENTIAL_NAMESPACE);
    $this->culture       = $storage->read(self::CULTURE_NAMESPACE);
    $this->lastRequest   = $storage->read(self::LAST_REQUEST_NAMESPACE);

    if ($this->authenticated == null)
    {
      $this->authenticated = false;
      $this->credentials   = array();
    }

    // Automatic logout if no request for more than SF_TIMEOUT
    if ((time() - $this->lastRequest) > SF_TIMEOUT)
    {
      if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfUser} automatic user logout');
      $this->clearCredentials();
      $this->setAuthenticated(false);
    }

    $this->lastRequest = time();

    if ($this->culture == null)
    {
      $this->culture = SF_DEFAULT_CULTURE;
    }

    // i18n
    if (SF_IS_I18N)
    {
      $context->getRequest()->setAttribute('gm', new sfMessageFormat($context->getUser()->getCulture()));
    }
  }

  public function shutdown ()
  {
    $storage = $this->getContext()->getStorage();

    // write culture to the storage
    $storage->write(self::CULTURE_NAMESPACE,      $this->culture);

    // write the last request time to the storage
    $storage->write(self::LAST_REQUEST_NAMESPACE, $this->lastRequest);

    $storage->write(self::AUTH_NAMESPACE,         $this->authenticated);
    $storage->write(self::CREDENTIAL_NAMESPACE,   $this->credentials);

    // call the parent shutdown method
    parent::shutdown();
  }
}

?>