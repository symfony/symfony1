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

  /**
   * Constructs a new sfUpgrade instance.
   *
   * @param sfTask A sfTask instance
   */
  public function __construct(sfTask $task)
  {
    $this->task = $task;
  }

  /**
   * Upgrades the current project from 1.0 to 1.1.
   */
  abstract public function upgrade();

  /**
   * Returns a finder that exclude upgrade scripts from being upgraded!
   *
   * @param  string   String directory or file or any (for both file and directory)
   *
   * @return sfFinder A sfFinder instance
   */
  public function getFinder($type)
  {
    return sfFinder::type($type)->prune('upgrade1.1');
  }

  /**
   * Forward all non existing methods to the task.
   *
   * @param  string The method name
   * @param  array  An array of arguments
   *
   * @return mixed  The return value of the task method call
   */
  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->task, $method), $arguments);
  }
}
