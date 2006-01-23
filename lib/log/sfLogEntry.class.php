<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogEntry.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogEntry
{
  private
    $priority,
    $priority_string,
    $time,
    $message,
    $elapsed_time,
    $type,
    $debug_stack;

  public function getType()
  {
    return $this->type;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function getPriority()
  {
    return $this->priority;
  }

  public function setPriority($priority)
  {
    $this->priority = $priority;
  }

  public function getPriorityString()
  {
    return $this->priority_string;
  }

  public function setPriorityString($priority_string)
  {
    $this->priority_string = $priority_string;
  }

  public function getElapsedTime()
  {
    return $this->elapsed_time;
  }

  public function setElapsedTime($elapsed_time)
  {
    $this->elapsed_time = $elapsed_time;
  }

  public function getTime()
  {
    return $this->time;
  }

  public function setTime($time)
  {
    $this->time = $time;
  }

  public function getMessage()
  {
    return $this->message;
  }

  public function setMessage($message)
  {
    $this->message = $message;
  }

  public function getDebugStack()
  {
    return $this->debug_stack;
  }

  public function setDebugStack($debug_stack)
  {
    $this->debug_stack = $debug_stack;
  }
}

?>