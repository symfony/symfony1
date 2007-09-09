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
    $context = null;

  /**
   * Initializes this view parameter holder.
   *
   * @param sfContext A sfContext instance.
   * @param array     An associative array of initialization parameters.
   * @param array     An associative array of options.
   *
   * @return Boolean  true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this view parameter holder.
   */
  public function initialize($context, $parameters = array(), $options = array())
  {
    $this->context = $context;

    $this->add($this->getGlobalAttributes());
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
   * Returns view attributes accessible for every view.
   *
   * @return array An array of view attributes
   */
  protected function getGlobalAttributes()
  {
    $attributes = array(
      'sf_context'  => $this->context,
      'sf_params'   => $this->context->getRequest()->getParameterHolder(),
      'sf_request'  => $this->context->getRequest(),
      'sf_response' => $this->context->getResponse(),
      'sf_user'     => $this->context->getUser(),
    );

    return $attributes;
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
    $tmp->context = null;

    return serialize($tmp->parameters);
  }

  /**
   * Unserializes a sfViewParameterHolder instance.
   */
  public function unserialize($serialized)
  {
    parent::unserialize($serialized);

    $this->context = sfContext::getInstance();
    $this->add($this->getGlobalAttributes());
  }
}
