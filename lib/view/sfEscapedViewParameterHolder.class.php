<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfEscapedViewParameterHolder stores all variables that will be available to the template.
 *
 * It also escapes variables with an escaping method.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfEscapedViewParameterHolder extends sfViewParameterHolder
{
  protected
    $escaping       = null,
    $escapingMethod = null;

  /**
   * Initializes this view parameter holder.
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance.
   * @param  array             An associative array of initialization parameters.
   * @param  array             An associative array of options.
   *
   * <b>Options:</b>
   *
   * # <b>escaping_strategy</b> - [bc]           - The escaping strategy (bc, both, on or off)
   * # <b>escaping_method</b>   - [ESC_ENTITIES] - The escaping method (ESC_RAW, ESC_ENTITIES, ESC_JS or ESC_JS_NO_ENTITIES)
   *
   * @return Boolean   true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this view parameter holder.
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $options = array())
  {
    parent::initialize($dispatcher, $parameters, $options);

    $this->setEscaping(isset($options['escaping_strategy']) ? $options['escaping_strategy'] : 'bc');
    $this->setEscapingMethod(isset($options['escaping_method']) ? $options['escaping_method'] : 'ESC_ENTITIES');
  }

  /**
   * Returns true if the current object acts as an escaper.
   *
   * @return Boolean true if the current object acts as an escaper, false otherwise
   */
  public function isEscaped()
  {
    return true;
  }

  /**
   * Returns an array representation of the view parameters.
   *
   * @return array An array of view parameters
   */
  public function toArray()
  {
    $attributes = array();

    $escapedData = sfOutputEscaper::escape($this->getEscapingMethod(), $this->getAll());

    switch ($this->getEscaping())
    {
      case 'bc':
        $attributes = $this->getAll();
        break;
      case 'both':
        foreach ($escapedData as $key => $value)
        {
          $attributes[$key] = $value;
        }
        break;
    }

    $attributes['sf_data'] = $escapedData;

    return $attributes;
  }

  /**
   * Gets the default escaping strategy associated with this view.
   *
   * The escaping strategy specifies how the variables get passed to the view.
   *
   * @return string the escaping strategy
   */
  public function getEscaping()
  {
    return $this->escaping;
  }

  /**
   * Sets the escape caracter mode.
   *
   * @param string Escape code
   */
  public function setEscaping($escaping)
  {
    $this->escaping = $escaping;
  }

  /**
   * Returns the name of the function that is to be used as the escaping method.
   *
   * If the escaping method is empty, then that is returned. The default value
   * specified by the sub-class will be used. If the method does not exist (in
   * the sense there is no define associated with the method) and exception is
   * thrown.
   *
   * @return string The escaping method as the name of the function to use
   *
   * @throws <b>sfConfigurationException</b> If the method does not exist
   */
  public function getEscapingMethod()
  {
    if (empty($this->escapingMethod))
    {
      return $this->escapingMethod;
    }

    if (!defined($this->escapingMethod))
    {
      throw new sfConfigurationException(sprintf('The escaping method "%s" is not available.', $this->escapingMethod));
    }

    return constant($this->escapingMethod);
  }

  /**
   * Sets the escaping method for the current view.
   *
   * @param string Method for escaping
   */
  public function setEscapingMethod($method)
  {
    $this->escapingMethod = $method;
  }

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    $this->set('_sf_escaping_method', $this->escapingMethod);
    $this->set('_sf_escaping', $this->escaping);

    $serialized = parent::serialize();

    $this->remove('_sf_escaping_method');
    $this->remove('_sf_escaping');

    return $serialized;
  }

  /**
   * Unserializes a sfViewParameterHolder instance.
   */
  public function unserialize($serialized)
  {
    parent::unserialize($serialized);

    $this->setEscapingMethod($this->remove('_sf_escaping_method'));
    $this->setEscaping($this->remove('_sf_escaping'));
  }
}
