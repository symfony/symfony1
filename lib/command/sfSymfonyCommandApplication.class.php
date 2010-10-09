<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSymfonyCommandApplication manages the symfony CLI.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfSymfonyCommandApplication.class.php 12499 2008-10-31 16:10:07Z Kris.Wallsmith $
 */
class sfSymfonyCommandApplication extends sfCommandApplication
{
  /**
   * Configures the current symfony command application.
   */
  public function configure()
  {
    if (!isset($this->options['symfony_lib_dir']))
    {
      throw new sfInitializationException('You must pass a "symfony_lib_dir" option.');
    }
    
    $configurationFile = getcwd().'/config/ProjectConfiguration.class.php';
    if (is_readable($configurationFile))
    {
      require_once $configurationFile;
      $configuration = new ProjectConfiguration(getcwd(), $this->dispatcher);
    }
    else
    {
      $configuration = new sfProjectConfiguration(getcwd(), $this->dispatcher);
    }

    // application
    $this->setName('symfony');
    $this->setVersion(SYMFONY_VERSION);

    $this->loadTasks($configuration);
  }

  /**
   * Runs the current application.
   *
   * @param mixed $options The command line options
   */
  public function run($options = null)
  {
    $this->handleOptions($options);
    $arguments = $this->commandManager->getArgumentValues();

    if (!isset($arguments['task']))
    {
      $arguments['task'] = 'list';
      $this->commandOptions .= $arguments['task'];
    }

    $this->currentTask = $this->getTaskToExecute($arguments['task']);

    if ($this->currentTask instanceof sfCommandApplicationTask)
    {
      $this->currentTask->setCommandApplication($this);
    }

    $ret = $this->currentTask->runFromCLI($this->commandManager, $this->commandOptions);

    $this->currentTask = null;

    return $ret;
  }

  /**
   * Loads all available tasks.
   *
   * Looks for tasks in the symfony core, the current project and all project plugins.
   *
   * @param sfProjectConfiguration $configuration The project configuration
   */
  protected function loadTasks(sfProjectConfiguration $configuration)
  {
    // Symfony core tasks
    $dirs = array(sfConfig::get('sf_symfony_lib_dir').'/task');

    // Plugin tasks
    foreach ($configuration->getPluginPaths() as $path)
    {
      if (is_dir($taskPath = $path.'/lib/task'))
      {
        $dirs[] = $taskPath;
      }
    }

    // project tasks
    $dirs[] = sfConfig::get('sf_lib_dir').'/task';

    // require tasks
    $finder = sfFinder::type('file')->name('*Task.class.php');
    foreach ($finder->in($dirs) as $task)
    {
      require_once $task;
    }
  }

  /**
   * @see sfCommandApplication
   */
  public function getLongVersion()
  {
    return sprintf('%s version %s (%s)', $this->getName(), $this->formatter->format($this->getVersion(), 'INFO'), sfConfig::get('sf_symfony_lib_dir'))."\n";
  }
}
