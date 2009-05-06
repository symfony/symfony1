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
class sfValidatorRegex extends sfValidatorString
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * pattern:    A regex pattern compatible with PCRE (required)
   *  * must_match: Whether the regex must match or not (true by default)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorString
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('pattern');
    $this->addOption('must_match', true);
  }

  /**
   * @see sfValidatorString
   */
  protected function doClean($value)
  {
    $clean = parent::doClean($value);

    if (
      ($this->getOption('must_match') && !preg_match($this->getOption('pattern'), $clean))
      ||
      (!$this->getOption('must_match') && preg_match($this->getOption('pattern'), $clean))
    )
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $clean;
  }
}
