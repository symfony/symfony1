<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
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
 * @author     Sean Kerr <sean@code-box.org>
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
    $options         = array(),
    $attributeHolder = null,
    $culture         = null,
    $storage         = null,
    $dispatcher      = null;

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    $this->initialize($dispatcher, $storage, $options);

    if ($this->options['auto_shutdown'])
    {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  /**
   * Initializes this sfUser.
   *
   * Available options:
   *
   *  * auto_shutdown:   Whether to automatically save the changes to the session (true by default)
   *  * culture:         The user culture
   *  * default_culture: The default user culture (en by default)
   *  * use_flash:       Whether to enable flash usage (false by default)
   *  * logging:         Whether to enable logging (false by default)
   *
   * @param sfEventDispatcher A sfEventDispatcher instance.
   * @param sfStorage         A sfStorage instance.
   * @param array             An associative array of options.
   *
   * @return Boolean          true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->storage    = $storage;

    $this->options = array_merge(array(
      'auto_shutdown'   => true,
      'culture'         => null,
      'default_culture' => 'en',
      'use_flash'       => false,
      'logging'         => false,
    ), $options);

    $this->attributeHolder = new sfNamespacedParameterHolder(self::ATTRIBUTE_NAMESPACE);

    // read attributes from storage
    $attributes = $storage->read(self::ATTRIBUTE_NAMESPACE);
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
    //  - use the default culture set in settings.yml
    $currentCulture = $storage->read(self::CULTURE_NAMESPACE);
    $this->setCulture(!is_null($this->options['culture']) ? $this->options['culture'] : (!is_null($currentCulture) ? $currentCulture : $this->options['default_culture']));

    // flag current flash to be removed at shutdown
    if ($this->options['use_flash'] && $names = $this->attributeHolder->getNames('symfony/user/sfUser/flash'))
    {
      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Flag old flash messages ("%s")', implode('", "', $names)))));
      }

      foreach ($names as $name)
      {
        $this->attributeHolder->set($name, true, 'symfony/user/sfUser/flash/remove');
      }
    }
  }

  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Sets the user culture.
   *
   * @param  string culture
   */
  public function setCulture($culture)
  {
    if ($this->culture != $culture)
    {
      $this->culture = $culture;

      $this->dispatcher->notify(new sfEvent($this, 'user.change_culture', array('culture' => $culture)));
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
    if (!$this->options['use_flash'])
    {
      return;
    }

    $this->setAttribute($name, $value, 'symfony/user/sfUser/flash');

    if ($persist)
    {
      // clear removal flag
      $this->attributeHolder->remove($name, null, 'symfony/user/sfUser/flash/remove');
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
    if (!$this->options['use_flash'])
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
    if (!$this->options['use_flash'])
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

  /**
   * Executes the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    // remove flash that are tagged to be removed
    if ($this->options['use_flash'] && $names = $this->attributeHolder->getNames('symfony/user/sfUser/flash/remove'))
    {
      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Remove old flash messages ("%s")', implode('", "', $names)))));
      }

      foreach ($names as $name)
      {
        $this->attributeHolder->remove($name, null, 'symfony/user/sfUser/flash');
        $this->attributeHolder->remove($name, null, 'symfony/user/sfUser/flash/remove');
      }
    }

    $attributes = array();
    foreach ($this->attributeHolder->getNamespaces() as $namespace)
    {
      $attributes[$namespace] = $this->attributeHolder->getAll($namespace);
    }

    // write attributes to the storage
    $this->storage->write(self::ATTRIBUTE_NAMESPACE, $attributes);

    // write culture to the storage
    $this->storage->write(self::CULTURE_NAMESPACE, $this->culture);

    session_write_close();
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string The method name
   * @param array  The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws <b>sfException</b> If the calls fails
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'user.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}
