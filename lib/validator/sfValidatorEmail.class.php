<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorEmail validates emails.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorEmail extends sfValidatorRegex
{
  /**
   * @see sfValidatorRegex
   */
  public function __construct($options = array(), $messages = array())
  {
    $options['pattern'] = '/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i';

    parent::__construct($options, $messages);
  }
}
