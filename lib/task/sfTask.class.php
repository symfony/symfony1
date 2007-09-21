<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for all tasks.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfTask
{
  protected
    $namespace            = '',
    $name                 = null,
    $aliases              = array(),
    $briefDescription     = '',
    $detailedDescription  = '',
    $arguments            = array(),
    $options              = array(),
    $commandApplication   = null;

  /**
   * Constructor.
   *
   * @param sfCommandApplication A sfCommandApplication object
   */
  public function __construct(sfCommandApplication $commandApplication = null, sfLogger $logger = null)
  {
    $this->initialize($commandApplication, $logger);

    $this->configure();
  }

  /**
   * Initializes the sfTask instance.
   *
   * @param sfCommandApplication A sfCommandApplication instance
   * @param sfLogger             A sfLogger instance
   *
   * @throws <b>sfFactoryException</b> If a task implementation instance cannot
   */
  public function initialize(sfCommandApplication $commandApplication = null, sfLogger $logger = null)
  {
    $this->commandApplication = $commandApplication;
    $this->setLogger($logger);
  }

  /**
   * Configures the current task.
   */
  protected function configure()
  {
  }

  /**
   * Runs the task from the CLI.
   *
   * @param sfCommandManager A sfCommandManager instance
   * @param mixed            The command line options
   */
  public function runFromCLI(sfCommandManager $commandManager, $options = null)
  {
    $commandManager->getArgumentSet()->addArguments($this->getArguments());
    $commandManager->getOptionSet()->addOptions($this->getOptions());

    return $this->doRun($commandManager, $options);
  }

  /**
   * Runs the task.
   *
   * @param array An array of arguments
   * @param array An array of options
   */
  public function run($arguments = array(), $options = array())
  {
    $commandManager = new sfCommandManager(new sfCommandArgumentSet($this->getArguments()), new sfCommandOptionSet($this->getOptions()));

    return $this->doRun($commandManager, array_merge($arguments, $options));
  }

  /**
   * Returns the argument objects.
   *
   * @return sfCommandArgument An array of sfCommandArgument objects.
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Adds an array of argument objects.
   *
   * @return sfCommandArgument An array of sfCommandArgument objects.
   */
  public function addArguments($arguments)
  {
    $this->arguments = array_merge($this->arguments, $arguments);
  }

  /**
   * Returns the options objects.
   *
   * @return sfCommandOption An array of sfCommandOption objects.
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Adds an array of option objects.
   *
   * @return sfCommandOption An array of sfCommandOption objects.
   */
  public function addOptions($options)
  {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * Returns the task namespace.
   *
   * @param string The task namespace
   */
  public function getNamespace()
  {
    return $this->namespace;
  }

  /**
   * Returns the task name
   *
   * @return string The task name
   */
  public function getName()
  {
    if ($this->name)
    {
      return $this->name;
    }

    $name = get_class($this);

    if ('sf' == substr($name, 0, 2))
    {
      $name = substr($name, 2);
    }

    if ('Task' == substr($name, -4))
    {
      $name = substr($name, 0, -4);
    }

    return str_replace('_', '-', sfInflector::underscore($name));
  }

  /**
   * Returns the fully qualified task name.
   *
   * @return string The fully qualified task name
   */
  final function getFullName()
  {
    return $this->getNamespace() ? $this->getNamespace().':'.$this->getName() : $this->getName();
  }

  /**
   * Returns the brief description for the task.
   *
   * @return string The brief description for the task
   */
  public function getBriefDescription()
  {
    return $this->briefDescription;
  }

  /**
   * Returns the detailed description for the task.
   *
   * It also formats special string like [...|COMMENT]
   * depending on the current logger.
   *
   * @return string The detailed description for the task
   */
  public function getDetailedDescription()
  {
    return preg_replace('/\[(.+?)\|(\w+)\]/se', '$this->format("$1", "$2")', $this->detailedDescription);
  }

  /**
   * Returns the aliases for the task.
   *
   * @return array An array of aliases for the task
   */
  public function getAliases()
  {
    return $this->aliases;
  }

  /**
   * Returns the synopsis for the task.
   *
   * @param string The synopsis
   */
  public function getSynopsis()
  {
    $options = array();
    foreach ($this->getOptions() as $option)
    {
      $shortcut = $option->getShortcut() ? sprintf('|-%s', $option->getShortcut()) : '';
      $options[] = sprintf('['.($option->isParameterRequired() ? '--%s%s="..."' : ($option->isParameterOptional() ? '--%s%s[="..."]' : '--%s%s')).']', $option->getName(), $shortcut);
    }

    $arguments = array();
    foreach ($this->getArguments() as $argument)
    {
      $arguments[] = sprintf($argument->isRequired() ? '%s' : '[%s]', $argument->getName().($argument->isArray() ? '1' : ''));

      if ($argument->isArray())
      {
        $arguments[] = '... [nameN]';
      }
    }

    return sprintf('%%s %s %s %s', $this->getFullName(), implode(' ', $options), implode(' ', $arguments));
  }

  /**
   * Sets the logger.
   *
   * @param sfLogger The logger object
   */
  public function setLogger(sfLogger $logger = null)
  {
    $this->logger = $logger;
  }

  /**
   * Gets the logger.
   *
   * @return sfLogger The logger object
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Logs a message.
   *
   * @param string  The message to log
   * @param integer The priority
   */
  public function log($message, $priority = sfLogger::INFO)
  {
    if (is_null($this->logger))
    {
      return;
    }

    $this->logger->log($message, $priority);
  }

  /**
   * Formats a string for a given type.
   *
   * @param  string The text message
   * @param  mixed  The message type (COMMENT, INFO, ERROR)
   *
   * @return string The formatted string
   */
  public function format($message, $type = 'INFO')
  {
    if (is_null($this->logger))
    {
      return;
    }

    if ($this->logger instanceof sfCommandLogger)
    {
      return $this->logger->format($message, $type);
    }
    else
    {
      return $message;
    }
  }

  /**
   * Formats a message within a section.
   *
   * @param string  The section name
   * @param string  The text message
   * @param integer The maximum size allowed for a line
   */
  public function formatSection($section, $text, $size = null)
  {
    if (is_null($this->logger))
    {
      return;
    }

    if ($this->logger instanceof sfCommandLogger)
    {
      return $this->logger->formatSection($section, $text, $size);
    }
    else
    {
      return $section.' '.$text;
    }
  }

  public function __get($key)
  {
    switch ($key)
    {
      case 'logger':
        if (!isset($this->logger))
        {
          $this->logger = is_null($this->commandApplication) ? null : $this->commandApplication->getLogger();
        }

        return $this->logger;
      default:
        trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $key), E_USER_NOTICE);
    }
  }

  protected function process(sfCommandManager $commandManager, $options)
  {
    $commandManager->process($options);
    if (!$commandManager->isValid())
    {
      throw new sfCommandArgumentsException(sprintf("The execution of task \"%s\" failed.\n- %s", $this->getFullName(), implode("\n- ", $commandManager->getErrors())));
    }
  }

  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->dispatcher->filter(new sfEvent($this, 'command.filter_options', array('command_manager' => $commandManager)), $options);

    $this->process($commandManager, $options);

    $event = new sfEvent($this, 'command.pre_command', array('arguments' => $commandManager->getArgumentValues(), 'options' => $commandManager->getOptionValues()));
    $this->dispatcher->notifyUntil($event);
    if ($event->isProcessed())
    {
      return $this->getReturnValue();
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent($this, 'command.post_command'));

    return $ret;
  }

  /**
   * Executes the current task.
   *
   * @param array An array of arguments
   * @param array An array of options
   */
   abstract protected function execute($arguments = array(), $options = array());
}
