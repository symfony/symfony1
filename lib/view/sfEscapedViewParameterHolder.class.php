<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A sfEscapedViewParameterHolder stores all variables that will be available to the template.
 *
 * It also escape all variables with an escaping method.
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
   * @param sfContext A sfContext instance.
   * @param array     An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this view parameter holder.
   */
  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->setEscaping(sfConfig::get('sf_escaping_strategy'));
    $this->setEscapingMethod(sfConfig::get('sf_escaping_method'));
  }

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
      throw new sfConfigurationException(sprintf('Escaping method "%s" is not available; perhaps another helper needs to be loaded in?', $this->escapingMethod));
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
}
