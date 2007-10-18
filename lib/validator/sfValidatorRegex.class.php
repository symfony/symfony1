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
   * @param string A regex pattern compatible with PCRE
   * @param array  An array of options
   * @param array  An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($pattern, $options = array(), $messages = array())
  {
    $options['pattern'] = $pattern;

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
}
