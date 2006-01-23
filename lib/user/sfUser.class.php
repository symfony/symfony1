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

  private
    $parameter_holder = null,
    $attribute_holder = null,
    $culture = null;

  protected
    $context          = null;

  /**
   * Retrieve the current application context.
   *
   * @return Context A Context instance.
   */
  public function getContext ()
  {
    return $this->context;
  }

  /**
   * Initialize this User.
   *
   * @param Context A Context instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise
   *              false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this User.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameter_holder = new sfParameterHolder();
    $this->parameter_holder->add($parameters);

    $this->attribute_holder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);

    // read attributes from storage
    $attributes = $this->getContext()->getStorage()->read(self::ATTRIBUTE_NAMESPACE);
    if (is_array($attributes))
    {
      foreach ($attributes as $namespace => $values)
      {
        $this->attribute_holder->add($values, $namespace);
      }
    }

    $culture = $storage->read(self::CULTURE_NAMESPACE);
    if ($this->culture == null)
    {
      $culture = sfConfig::get('sf_i18n_default_culture');
    }

    $this->setCulture($culture);
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
  public static function newInstance ($class)
  {
    // the class exists
    $object = new $class();

    if (!($object instanceof sfUser))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfUser';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }

  /**
   * Sets culture.
   *
   * @param  string culture
   */
  public function setCulture ($culture)
  {
    if ($this->culture != $culture)
    {
      $this->culture = $culture;

      // change the message format object with the new culture
      if (sfConfig::get('sf_i18n'))
      {
        $this->context->getI18N()->setCulture($culture);
      }
    }
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
    return $this->parameter_holder;
  }

  public function getAttributeHolder()
  {
    return $this->attribute_holder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attribute_holder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attribute_holder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attribute_holder->set($name, $value, $ns);
  }

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameter_holder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameter_holder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameter_holder->set($name, $value, $ns);
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown ()
  {
    $storage = $this->getContext()->getStorage();

    $attributes = array();
    foreach ($this->attribute_holder->getNamespaces() as $namespace)
    {
      $attributes[$namespace] = $this->attribute_holder->getAll($namespace);
    }

    // write attributes to the storage
    $storage->write(self::ATTRIBUTE_NAMESPACE, $attributes);

    // write culture to the storage
    $storage->write(self::CULTURE_NAMESPACE, $this->culture);
  }
}

?>