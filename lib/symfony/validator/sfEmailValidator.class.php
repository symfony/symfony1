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
 * sfEmailValidator verifies a parameter contains a value that qualifies as an
 * email address.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfEmailValidator.class.php 432 2005-09-07 12:30:24Z fabien $
 */
class sfEmailValidator extends sfValidator
{
  /**
   * Execute this validator.
   *
   * @param mixed A file or parameter value/array.
   * @param error An error message reference.
   *
   * @return bool true, if this validator executes successfully, otherwise
   *              false.
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public function execute (&$value, &$error)
  {
    if (!preg_match('~^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$~i', $value))
    {
      $error = $this->getParameterHolder()->get('email_error');
      return false;
    }

    return true;
  }

  public function initialize ($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context);

    // set defaults
    $this->getParameterHolder()->set('email_error', 'Invalid input');

    $this->getParameterHolder()->add($parameters);

    return true;
  }
}

?>
