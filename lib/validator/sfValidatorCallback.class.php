<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCallback validates an input value if the given callback does not throw a sfValidatorError.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorCallback extends sfValidator
{
  protected
    $callback = null;

  /**
   * Constructor.
   *
   * The first argument can be any valid PHP callback.
   *
   * @param mixed A PHP callback
   * @param array An array of options
   * @param array An array of error messages
   *
   * @see sfValidator
   */
  public function __construct($callback, $options = array(), $messages = array())
  {
    $this->callback = $callback;

    parent::__construct($options, $messages);
  }

  /**
   * @see sfValidator
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->options['required'] = false;
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    return call_user_func($this->callback, $this, $value);
  }
}
