<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for upgrade classes.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfUpgrade
{
  protected
    $task = null;

  public function __construct(sfTask $task)
  {
    $this->task = $task;
  }

  abstract public function upgrade();

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->task, $method), $arguments);
  }
}
