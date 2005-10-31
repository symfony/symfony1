<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidator allows you to apply constraints to user entered parameters.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfValidator
{
  private
    $parameter_holder = null,
    $context = null;

  /**
   * Execute this validator.
   *
   * @param mixed A file or parameter value/array.
   * @param string An error message reference.
   *
   * @return bool true, if this validator executes successfully, otherwise false.
   */
  abstract function execute (&$value, &$error);

  /**
   * Retrieve the current application context.
   *
   * @return sfContext The current sfContext instance.
   */
  public final function getContext ()
  {
    return $this->context;
  }

  /**
   * Initialize this validator.
   *
   * @param sfContext The current application context.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameter_holder = new sfParameterHolder();
    $this->parameter_holder->add($parameters);

    return true;
  }

  public function getParameterHolder ()
  {
    return $this->parameter_holder;
  }
}

?>