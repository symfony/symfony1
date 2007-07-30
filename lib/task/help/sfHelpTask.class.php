<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Displays help for a task.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfHelpTask extends sfTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('task_name', sfCommandArgument::OPTIONAL, 'The task name', 'help'),
    ));

    $this->aliases = array('h');

    $this->briefDescription = 'Displays help for a task';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!isset($this->commandApplication))
    {
      throw new sfCommandException('You can only launch this task from the command line.');
    }

    $task = $this->commandApplication->getTask($arguments['task_name']);

    $this->log($this->format("Usage:\n", 'COMMENT'));
    $this->log($this->format(sprintf(' '.$task->getSynopsis(), is_null($this->commandApplication) ? '' : $this->commandApplication->getName()))."\n\n");

    // find the largest option or argument name
    $max = 0;
    foreach ($task->getOptions() as $option)
    {
      $max = strlen($option->getName()) + 2 > $max ? strlen($option->getName()) + 2 : $max;
    }
    foreach ($task->getArguments() as $argument)
    {
      $max = strlen($argument->getName()) > $max ? strlen($argument->getName()) : $max;
    }
    $max += strlen($this->format(' ', 'INFO'));

    if ($task->getAliases())
    {
      $this->log($this->format("Aliases:\n", 'COMMENT').' '.$this->format(implode(', ', $task->getAliases()), 'INFO')."\n\n");
    }

    if ($task->getArguments())
    {
      $this->log($this->format("Arguments:\n", 'COMMENT'));
      foreach ($task->getArguments() as $argument)
      {
        $default = !is_null($argument->getDefault()) && (!is_array($argument->getDefault()) || count($argument->getDefault())) ? $this->format(sprintf(' (default: %s)', is_array($argument->getDefault()) ? str_replace("\n", '', print_r($argument->getDefault(), true)): $argument->getDefault()), 'COMMENT') : '';
        $this->log(sprintf(" %-${max}s %s%s\n", $this->format($argument->getName(), 'INFO'), $argument->getHelp(), $default));
      }

      $this->log("\n");
    }

    if ($task->getOptions())
    {
      $this->log($this->format("Options:\n", 'COMMENT'));

      foreach ($task->getOptions() as $option)
      {
        $default = $option->acceptParameter() && !is_null($option->getDefault()) && (!is_array($option->getDefault()) || count($option->getDefault())) ? $this->format(sprintf(' (default: %s)', is_array($option->getDefault()) ? str_replace("\n", '', print_r($option->getDefault(), true)): $option->getDefault()), 'COMMENT') : '';
        $multiple = $option->isArray() ? $this->format(' (multiple values allowed)', 'COMMENT') : '';
        $this->log(sprintf(' %-'.$max.'s %s%s%s%s', $this->format('--'.$option->getName(), 'INFO'), $option->getShortcut() ? sprintf('(-%s) ', $option->getShortcut()) : '', $option->getHelp(), $default, $multiple)."\n");
      }

      $this->log("\n");
    }

    if ($detailedDescription = $task->getDetailedDescription())
    {
      $this->log($this->format("Description:\n", 'COMMENT'));

      $this->log(' '.implode("\n ", explode("\n", $detailedDescription))."\n");
    }
  }
}
