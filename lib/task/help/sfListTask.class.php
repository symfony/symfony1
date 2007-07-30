<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Lists tasks.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfListTask extends sfTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('namespace', sfCommandArgument::OPTIONAL, 'The namespace name'),
    ));

    $this->briefDescription = 'Lists tasks';

    $this->detailedDescription = <<<EOF
The [list|INFO] task lists all tasks:

  [./symfony list|INFO]

You can also display the tasks for a specific namespace:

  [./symfony list test|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $tasks = array();
    foreach ($this->commandApplication->getTasks() as $name => $task)
    {
      if ($arguments['namespace'] && $arguments['namespace'] != $task->getNamespace())
      {
        continue;
      }

      if ($name != $task->getFullName())
      {
        // it is an alias
        continue;
      }

      if (!$task->getNamespace())
      {
        $name = '_default:'.$name;
      }

      $tasks[$name] = $task;
    }

    $width = 0;
    foreach ($tasks as $name => $task)
    {
      $width = strlen($task->getName()) > $width ? strlen($task->getName()) : $width;
    }
    $width += strlen($this->format('  ', 'INFO'));

    if ($arguments['namespace'])
    {
      $this->log($this->format(sprintf("Available tasks for the \"%s\" namespace:\n", $arguments['namespace']), 'COMMENT'));
    }
    else
    {
      $this->log($this->format("Available tasks:\n", 'COMMENT'));
    }

    // display tasks
    ksort($tasks);
    $currentNamespace = '';
    foreach ($tasks as $name => $task)
    {
      if (!$arguments['namespace'] && $currentNamespace != $task->getNamespace())
      {
        $currentNamespace = $task->getNamespace();
        $this->log(sprintf("%s\n", $this->format($task->getNamespace(), 'COMMENT')));
      }

      $aliases = $task->getAliases() ? $this->format(' ('.implode(', ', $task->getAliases()).')', 'COMMENT') : '';

      $this->log(sprintf("  %-${width}s %s%s\n", $this->format(':'.$task->getName(), 'INFO'), $task->getBriefDescription(), $aliases));
    }
  }
}
