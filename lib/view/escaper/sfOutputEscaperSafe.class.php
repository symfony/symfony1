<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Marks a variable as being safe for output.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfOutputEscaperSafe
{
  protected
    $values = null;

  /**
   * Constructor.
   *
   * @param mixed The value to mark as safe
   */
  public function __construct($value)
  {
    $this->value = $value;
  }

  public function __toString()
  {
    return $this->value;
  }

  /**
   * Returns the embedded value.
   *
   * @return mixed The embedded value
   */
  public function getValue()
  {
    return $this->value;
  }
}
