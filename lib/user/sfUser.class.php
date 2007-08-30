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
 *
 * sfUser wraps a client session and provides accessor methods for user
 * attributes. It also makes storing and retrieving multiple page form data
 * rather easy by allowing user attributes to be stored in namespaces, which
 * help organize data.
 *
 * @package    symfony
 * @subpackage user
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfUser
{
  /**
   * The namespace under which attributes will be stored.
   */
  const ATTRIBUTE_NAMESPACE = 'symfony/user/sfUser/attributes';

  const CULTURE_NAMESPACE = 'symfony/user/sfUser/culture';

  protected
    $parameterHolder = null,
    $attributeHolder = null,
    $culture         = null,
    $context         = null;

  /**
   * Retrieve the current application context.
   *
   * @return Context A Context instance.
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Initialize this User.
   *
   * @param sfContext A sfContext instance.
   * @param array     An associative array of initialization parameters.
   *
   * @return Boolean  true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfUser.
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    $this->attributeHolder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);

    // read attributes from storage
    $attributes = $context->getStorage()->read(self::ATTRIBUTE_NAMESPACE);
    if (is_array($attributes))
    {
      foreach ($attributes as $namespace => $values)
      {
        $this->attributeHolder->add($values, $namespace);
      }
    }

    // set the user culture to sf_culture parameter if present in the request
    // otherwise
    //  - use the culture defined in the user session
    //  - use the default culture set in i18n.yml
    if (!($culture = $context->getRequest()->getParameter('sf_culture')))
    {
      if (null === ($culture = $context->getStorage()->read(self::CULTURE_NAMESPACE)))
      {
        $culture = sfConfig::get('sf_i18n_default_culture', 'en');
      }
    }

    $this->setCulture($culture);

    // flag current flash to be removed at shutdown
    if ($this->parameterHolder->get('use_flash', false) && $names = $this->attributeHolder->getNames('symfony/user/sfUser/flash'))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->context->getLogger()->info(sprintf('{sfUser} flag old flash messages ("%s")', implode('", "', $names)));
      }

      foreach ($names as $name)
      {
        $this->attributeHolder->set($name, true, 'symfony/user/sfUser/flash/remove');
      }
    }
  }

  /**
   * Retrieve a new sfUser implementation instance.
   *
   * @param string A sfUser implementation name
   *
   * @return User A sfUser implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a user implementation instance cannot
   */
  public static function newInstance($class)
  {
    $object = new $class();

    if (!$object instanceof sfUser)
    {
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfUser.', $class));
    }

    return $object;
  }

  /**
   * Sets culture.
   *
   * @param  string culture
   */
  public function setCulture($culture)
  {
    if ($this->culture != $culture)
    {
      $this->culture = $culture;

      // change the message format object with the new culture
      if (sfConfig::get('sf_i18n'))
      {
        $this->context->getI18N()->setCulture($culture);
      }

      // change the culture in the routing default parameters
      $this->context->getRouting()->setDefaultParameter('sf_culture', $culture);
    }
  }

  /**
   * Sets a flash variable that will be passed to the very next action.
   *
   * @param  string  The name of the flash variable
   * @param  string  The value of the flash variable
   * @param  Boolean true if the flash have to persist for the following request (true by default)
   */
  public function setFlash($name, $value, $persist = true)
  {
    if (!$this->parameterHolder->get('use_flash', false))
    {
      return;
    }

    $this->setAttribute($name, $value, 'symfony/user/sfUser/flash');

    if ($persist)
    {
      // clear removal flag
      $this->attributeHolder->remove($name, 'symfony/user/sfUser/flash/remove');
    }
    else
    {
      $this->setAttribute($name, true, 'symfony/user/sfUser/flash/remove');
    }
  }

  /**
   * Gets a flash variable.
   *
   * @param  string The name of the flash variable
   *
   * @return mixed  The value of the flash variable
   */
  public function getFlash($name, $default = null)
  {
    if (!$this->parameterHolder->get('use_flash', false))
    {
      return $default;
    }

    return $this->getAttribute($name, $default, 'symfony/user/sfUser/flash');
  }

  /**
   * Returns true if a flash variable of the specified name exists.
   * 
   * @param  string  The name of the flash variable
   *
   * @return Boolean true if the variable exists, false otherwise
   */
  public function hasFlash($name)
  {
    if (!$this->parameterHolder->get('use_flash', false))
    {
      return false;
    }

    return $this->hasAttribute($name, 'symfony/user/sfUser/flash');
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

  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attributeHolder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attributeHolder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attributeHolder->set($name, $value, $ns);
  }

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameterHolder->set($name, $value, $ns);
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    // remove flash that are tagged to be removed
    if ($this->parameterHolder->get('use_flash', false) && $names = $this->attributeHolder->getNames('symfony/user/sfUser/flash/remove'))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->context->getLogger()->info(sprintf('{sfUser} remove old flash messages ("%s")', implode('", "', $names)));
      }

      foreach ($names as $name)
      {
        $this->attributeHolder->remove($name, 'symfony/user/sfUser/flash');
        $this->attributeHolder->remove($name, 'symfony/user/sfUser/flash/remove');
      }
    }

    $storage = $this->context->getStorage();

    $attributes = array();
    foreach ($this->attributeHolder->getNamespaces() as $namespace)
    {
      $attributes[$namespace] = $this->attributeHolder->getAll($namespace);
    }

    // write attributes to the storage
    $storage->write(self::ATTRIBUTE_NAMESPACE, $attributes);

    // write culture to the storage
    $storage->write(self::CULTURE_NAMESPACE, $this->culture);

    session_write_close();
  }

  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfUser:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfUser::%s.', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
