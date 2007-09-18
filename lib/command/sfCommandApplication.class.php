<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * .
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommandApplication
{
  protected static $OPTIONS;

  protected
    $commandManager = null,
    $options        = null,
    $trace          = false,
    $verbose        = true,
    $dryrun         = false,
    $nowrite        = false,
    $name           = 'UNKNOWN',
    $version        = 'UNKNOWN',
    $tasks          = array(),
    $currentTask    = null,
    $dispatcher     = null,
    $logger         = null;

  /**
   * Constructor.
   *
   * @param object A logger that extends sfLogger
   */
  public function __construct(sfLogger $logger = null, sfEventDispatcher $dispatcher = null)
  {
    $this->logger = $logger;
    require dirname(__FILE__).'/../event/sfEvent.class.php';
    require dirname(__FILE__).'/../event/sfEventDispatcher.class.php';
    $this->dispatcher = is_null($dispatcher) ? new sfEventDispatcher() : $dispatcher;

    $this->fixCgi();
  }

  /**
   * Sets the logger.
   *
   * @param object A logger that extends sfLogger
   */
  public function setLogger(sfLogger $logger = null)
  {
    $this->logger = $logger;
  }

  /**
   * Gets the current logger object.
   *
   * @return object The logger object
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Registers an array of task objects.
   *
   * @param array An array of tasks
   */
  public function registerTasks($tasks)
  {
    foreach ($tasks as $task)
    {
      $this->registerTask($task);
    }
  }

  /**
   * Registers a task object.
   *
   * @param sfTask An sfTask object
   */
  public function registerTask(sfTask $task)
  {
    if (isset($this->tasks[$task->getFullName()]))
    {
      throw new sfCommandException(sprintf('The task named "%s" in "%s" task is already registered by the "%s" task.', $task->getFullName(), get_class($task), get_class($this->tasks[$task->getFullName()])));
    }

    $this->tasks[$task->getFullName()] = $task;

    foreach ($task->getAliases() as $alias)
    {
      if (isset($this->tasks[$alias]))
      {
        throw new sfCommandException(sprintf('A task named "%s" is already registered.', $alias));
      }

      $this->tasks[$alias] = $task;
    }
  }

  /**
   * Returns all registered tasks.
   *
   * @return array An array of sfTask objects
   */
  public function getTasks()
  {
    return $this->tasks;
  }

  /**
   * Returns a registered task by name or alias.
   *
   * @param string  The task name or alias
   *
   * @return sfTask An sfTask object
   */
  public function getTask($name)
  {
    if (!isset($this->tasks[$name]))
    {
      throw new sfCommandException(sprintf('The task "%s" does not exist.', $name));
    }

    return $this->tasks[$name];
  }

  /**
   * Runs the current application.
   *
   * @param mixed The command line options
   */
  public function run($options = null)
  {
    $this->handleOptions($options);
    $arguments = $this->commandManager->getArgumentValues();

    if (!$this->isVerbose())
    {
      $this->setLogger(null);
    }

    if (!isset($arguments['task']))
    {
      $arguments['task'] = 'list';
      $this->commandOptions .= $arguments['task'];
    }

    $this->currentTask = $this->getTaskToExecute($arguments['task']);

    $ret = $this->currentTask->runFromCLI($this->commandManager, $this->commandOptions);

    $this->currentTask = null;

    return $ret;
  }

  /**
   * Gets the name of the application.
   *
   * @return string The application name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Sets the application name.
   *
   * @param string The application name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Gets the application version.
   *
   * @return string The application version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Sets the application version.
   *
   * @param string The application version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * Returns whether the application must be verbose.
   *
   * @return Boolean true if the application must be verbose, false otherwise
   */
  public function isVerbose()
  {
    return $this->verbose;
  }

  /**
   * Returns whether the application must activate the trace.
   *
   * @return Boolean true if the application must activate the trace, false otherwise
   */
  public function withTrace()
  {
    return $this->trace;
  }

  /*
   * Returns whether the application must run in dry mode.
   *
   * @return Boolean true if the application must run in dry mode, false otherwise
   */
  public function isDryrun()
  {
    return $this->dryrun;
  }

  /**
   * Outputs a help message for the current application.
   */
  public function help()
  {
    if (is_null($this->getLogger()))
    {
      return;
    }

    $this->logger->log(sprintf("%s [options] task_name [arguments]\n", $this->getName()));

    $this->logger->log("\nAvailable options:\n");

    foreach ($this->commandManager->getOptionSet()->getOptions() as $option)
    {
      $this->logger->log(sprintf("  %-10s (%s) %s\n", $option->getName(), $option->getShortcut(), $option->getHelp()));
    }
  }

  /**
   * Parses and handles command line options.
   *
   * @param mixed The command line options
   */
  protected function handleOptions($options = null)
  {
    $argumentSet = new sfCommandArgumentSet(array(
      new sfCommandArgument('task', sfCommandArgument::REQUIRED, 'The task to execute'),
    ));
    $optionSet = new sfCommandOptionSet(array(
      new sfCommandOption('--dry-run', '-n', sfCommandOption::PARAMETER_NONE, 'Do a dry run without executing actions.'),
      new sfCommandOption('--help',    '-H', sfCommandOption::PARAMETER_NONE, 'Display this help message.'),
      new sfCommandOption('--quiet',   '-q', sfCommandOption::PARAMETER_NONE, 'Do not log messages to standard output.'),
      new sfCommandOption('--trace',   '-t', sfCommandOption::PARAMETER_NONE, 'Turn on invoke/execute tracing, enable full backtrace.'),
      new sfCommandOption('--version', '-V', sfCommandOption::PARAMETER_NONE, 'Display the program version.'),
    ));
    $this->commandManager = new sfCommandManager($argumentSet, $optionSet);
    $this->commandManager->process($options);
    foreach ($this->commandManager->getOptionValues() as $opt => $value)
    {
      if (false === $value)
      {
        continue;
      }

      switch ($opt)
      {
        case 'dry-run':
          $this->verbose = true;
          $this->nowrite = true;
          $this->dryrun = true;
          $this->trace = true;
          break;
        case 'help':
          $this->help();
          exit();
        case 'quiet':
          $this->verbose = false;
          break;
        case 'trace':
          $this->trace = true;
          $this->verbose = true;
          break;
        case 'version':
          echo sprintf('%s version %s', $this->getName(), $this->logger->format($this->getVersion(), 'INFO'))."\n";
          exit(0);
      }
    }

    $this->commandOptions = $options;
  }

  /**
   * Renders an exception.
   *
   * @param Exception An exception object
   * @param sfTask    The current sfTask object
   */
  public function renderException($e)
  {
    $title = sprintf('  [%s]  ', get_class($e));
    $len = $this->strlen($title);
    $lines = array();
    foreach (explode("\n", $e->getMessage()) as $line)
    {
      $lines[] = sprintf('  %s  ', $line);
      $len = max($this->strlen($line) + 4, $len);
    }

    $messages = array(str_repeat(' ', $len));

    if ($this->trace)
    {
      $messages[] = $title.str_repeat(' ', $len - $this->strlen($title));
    }

    foreach ($lines as $line)
    {
      $messages[] = $line.str_repeat(' ', $len - $this->strlen($line));
    }

    $messages[] = str_repeat(' ', $len);

    fwrite(STDERR, "\n");
    foreach ($messages as $message)
    {
      fwrite(STDERR, $this->logger->format($message, 'ERROR', STDERR)."\n");
    }
    fwrite(STDERR, "\n");

    if (!is_null($this->currentTask) && $e instanceof sfCommandArgumentsException)
    {
      fwrite(STDERR, $this->logger->format(sprintf($this->currentTask->getSynopsis(), $this->getName()), 'INFO', STDERR)."\n");
      fwrite(STDERR, "\n");
    }

    if ($this->trace)
    {
      fwrite(STDERR, $this->logger->format("Exception trace:\n", 'COMMENT'));

      // exception related properties
      $trace = $e->getTrace();
      array_unshift($trace, array(
        'function' => '',
        'file'     => $e->getFile() != null ? $e->getFile() : 'n/a',
        'line'     => $e->getLine() != null ? $e->getLine() : 'n/a',
        'args'     => array(),
      ));

      for ($i = 0, $count = count($trace); $i < $count; $i++)
      {
        $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
        $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
        $function = $trace[$i]['function'];
        $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
        $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

        fwrite(STDERR, sprintf(" %s%s%s at %s:%s\n", $class, $type, $function, $this->logger->format($file, 'INFO', STDERR), $this->logger->format($line, 'INFO', STDERR)));
      }

      fwrite(STDERR, "\n");
    }
  }

  /**
   * Gets a task from a task name or a shortcut.
   *
   * @param  string The task name or a task shortcut
   *
   * @return sfTask A sfTask object
   */
  protected function getTaskToExecute($name)
  {
    // namespace
    if (false !== $pos = strpos($name, ':'))
    {
      $namespace = substr($name, 0, $pos);
      $name = substr($name, $pos + 1);

      $namespaces = array();
      foreach ($this->tasks as $task)
      {
        if ($task->getNamespace() && !in_array($task->getNamespace(), $namespaces))
        {
          $namespaces[] = $task->getNamespace();
        }
      }
      $abbrev = $this->getAbbreviations($namespaces);

      if (!isset($abbrev[$namespace]))
      {
        throw new sfCommandException(sprintf('There is no task defined in the "%s" namespace.', $namespace));
      }
      else if (count($abbrev[$namespace]) > 1)
      {
        throw new sfCommandException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, implode(', ', $abbrev[$namespace])));
      }
      else
      {
        $namespace = $abbrev[$namespace][0];
      }
    }
    else
    {
      $namespace = '';
    }

    // name
    $tasks = array();
    foreach ($this->tasks as $taskName => $task)
    {
      if ($taskName == $task->getFullName() && $task->getNamespace() == $namespace)
      {
        $tasks[] = $task->getName();
      }
    }

    $abbrev = $this->getAbbreviations($tasks);
    if (isset($abbrev[$name]) && count($abbrev[$name]) == 1)
    {
      return $this->getTask($namespace ? $namespace.':'.$abbrev[$name][0] : $abbrev[$name][0]);
    }

    // aliases
    $aliases = array();
    foreach ($this->tasks as $taskName => $task)
    {
      if ($taskName == $task->getFullName())
      {
        foreach ($task->getAliases() as $alias)
        {
          $aliases[] = $alias;
        }
      }
    }

    $abbrev = $this->getAbbreviations($aliases);
    if (!isset($abbrev[$name]))
    {
      throw new sfCommandException(sprintf('Task "%s" is not defined.', $name));
    }
    else if (count($abbrev[$name]) > 1)
    {
      throw new sfCommandException(sprintf('Task "%s" is ambiguous (%s).', $name, implode(', ', $abbrev[$name])));
    }
    else
    {
      return $this->getTask($abbrev[$name][0]);
    }
  }

  protected function strlen($string)
  {
    return function_exists('mb_strlen') ? mb_strlen($string) : strlen($string);
  }

  /**
   * Fixes php behavior if using cgi php.
   *
   * @see http://www.sitepoint.com/article/php-command-line-1/3
   */
  protected function fixCgi()
  {
    if (false === strpos(PHP_SAPI, 'cgi'))
    {
      return;
    }

    // handle output buffering
    @ob_end_flush();
    ob_implicit_flush(true);

    // PHP ini settings
    set_time_limit(0);
    ini_set('track_errors', true);
    ini_set('html_errors', false);
    ini_set('magic_quotes_runtime', false);

    // define stream constants
    define('STDIN',  fopen('php://stdin',  'r'));
    define('STDOUT', fopen('php://stdout', 'w'));
    define('STDERR', fopen('php://stderr', 'w'));

    // change directory
    if (isset($_SERVER['PWD']))
    {
      chdir($_SERVER['PWD']);
    }

    // close the streams on script termination
    register_shutdown_function(create_function('', 'fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true;'));
  }

  /**
   * Returns an array of possible abbreviations given a set of names.
   *
   * @see Text::Abbrev perl module for the algorithm
   */
  protected function getAbbreviations($names)
  {
    $abbrevs = array();
    $table   = array();

    foreach ($names as $name)
    {
      for ($len = strlen($name) - 1; $len > 0; --$len)
      {
        $abbrev = substr($name, 0, $len);
        if (!array_key_exists($abbrev, $table))
        {
          $table[$abbrev] = 1;
        }
        else
        {
          ++$table[$abbrev];
        }

        $seen = $table[$abbrev];
        if ($seen == 1)
        {
          // We're the first word so far to have this abbreviation.
          $abbrevs[$abbrev] = array($name);
        }
        else if ($seen == 2)
        {
          // We're the second word to have this abbreviation, so we can't use it.
          // unset($abbrevs[$abbrev]);
          $abbrevs[$abbrev][] = $name;
        }
        else
        {
          // We're the third word to have this abbreviation, so skip to the next word.
          continue;
        }
      }
    }

    // Non-abbreviations always get entered, even if they aren't unique
    foreach ($names as $name)
    {
      $abbrevs[$name] = array($name);
    }

    return $abbrevs;
  }
}
