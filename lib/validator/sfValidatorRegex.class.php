<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorRegex validates a value with a regular expression.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorRegex extends sfValidator
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * pattern: A regex pattern compatible with PCRE
   *
   * @see sfValidator
   */
  public function __construct($options = array(), $messages = array())
  {
    if (!isset($options['pattern']))
    {
      throw new sfException('The "pattern" option is mandatory.');
    }

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    if (!preg_match($this->getOption('pattern'), $clean))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $clean;
  }

  /**
   * @see sfValidator
   */
  protected function getOptionsWithoutDefaults()
  {
    return parent::getOptionsWithoutDefaults(array('pattern' => array('--fake--')));
  }

  /**
   * @see sfValidator
   */
  protected function getMessagesWithoutDefaults()
  {
    return parent::getMessagesWithoutDefaults(array('pattern' => array('--fake--')));
  }
}
