<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A Template Context stores all parameters that will be available to templates.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfViewParameterHolder extends sfParameterHolder
{
  protected
    $dispatcher = null;

  /**
   * Initializes this view parameter holder.
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance.
   * @param  array             An associative array of initialization parameters.
   * @param  array             An associative array of options.
   *
   * @return Boolean  true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this view parameter holder.
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $options = array())
  {
    $this->dispatcher = $dispatcher;

    $event = $dispatcher->filter(new sfEvent($this, 'template.filter_parameters'), $parameters);
    $parameters = $event->getReturnValue();

    $this->add($parameters);
  }

  /**
   * Returns an array representation of the view parameters.
   *
   * @return array An array of view parameters
   */
  public function toArray()
  {
    return $this->getAll();
  }

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    $tmp = clone $this;
    foreach ($tmp->getNames() as $key)
    {
      if (0 === strpos($key, 'sf_'))
      {
        $tmp->remove($key);
      }
    }
    $tmp->dispatcher = null;

    return serialize($tmp->getAll());
  }

  /**
   * Unserializes a sfViewParameterHolder instance.
   */
  public function unserialize($serialized)
  {
    parent::unserialize($serialized);

    $this->initialize(sfContext::hasInstance() ? sfContext::getInstance()->getEventDispatcher() : new sfEventDispatcher());
  }
}
