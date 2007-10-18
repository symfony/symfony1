<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPass is an identity validator. It simply returns the value unmodified. 
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorPass extends sfValidator
{
  /**
   * @see sfValidator
   */
  public function clean($value)
  {
    return $this->doClean($value);
  }

  /**
   * @see sfValidator
   */
  protected function doClean($value)
  {
    return $value;
  }
}
