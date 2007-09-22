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
class sfHelpTask extends sfCommandApplicationTask
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

    $messages = array();

    $messages[] = $this->formatter->format("Usage:\n", 'COMMENT');
    $messages[] = $this->formatter->format(sprintf(' '.$task->getSynopsis(), is_null($this->commandApplication) ? '' : $this->commandApplication->getName()))."\n\n";

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
    $max += strlen($this->formatter->format(' ', 'INFO'));

    if ($task->getAliases())
    {
      $messages[] = $this->formatter->format("Aliases:\n", 'COMMENT').' '.$this->formatter->format(implode(', ', $task->getAliases()), 'INFO')."\n\n";
    }

    if ($task->getArguments())
    {
      $messages[] = $this->formatter->format("Arguments:\n", 'COMMENT');
      foreach ($task->getArguments() as $argument)
      {
        $default = !is_null($argument->getDefault()) && (!is_array($argument->getDefault()) || count($argument->getDefault())) ? $this->formatter->format(sprintf(' (default: %s)', is_array($argument->getDefault()) ? str_replace("\n", '', print_r($argument->getDefault(), true)): $argument->getDefault()), 'COMMENT') : '';
        $messages[] = sprintf(" %-${max}s %s%s\n", $this->formatter->format($argument->getName(), 'INFO'), $argument->getHelp(), $default);
      }

      $messages[] = "\n";
    }

    if ($task->getOptions())
    {
      $messages[] = $this->formatter->format("Options:\n", 'COMMENT');

      foreach ($task->getOptions() as $option)
      {
        $default = $option->acceptParameter() && !is_null($option->getDefault()) && (!is_array($option->getDefault()) || count($option->getDefault())) ? $this->formatter->format(sprintf(' (default: %s)', is_array($option->getDefault()) ? str_replace("\n", '', print_r($option->getDefault(), true)): $option->getDefault()), 'COMMENT') : '';
        $multiple = $option->isArray() ? $this->formatter->format(' (multiple values allowed)', 'COMMENT') : '';
        $messages[] = sprintf(' %-'.$max.'s %s%s%s%s', $this->formatter->format('--'.$option->getName(), 'INFO'), $option->getShortcut() ? sprintf('(-%s) ', $option->getShortcut()) : '', $option->getHelp(), $default, $multiple)."\n";
      }

      $messages[] = "\n";
    }

    if ($detailedDescription = $task->getDetailedDescription())
    {
      $messages[] = $this->formatter->format("Description:\n", 'COMMENT');

      $messages[] = ' '.implode("\n ", explode("\n", $detailedDescription))."\n";
    }

    $this->dispatcher->notify(new sfEvent($this, 'command.log', $messages));
  }
}
