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
 * @version    SVN: $Id$
 */
class sfSymfonyCommandApplication extends sfCommandApplication
{
  /**
   * Configures the current symfony command application.
   *
   * @param string The symfony lib directory
   */
  public function configure()
  {
    if (!isset($this->options['symfony_lib_dir']))
    {
      throw new sfInitializationException('You must pass a "symfony_lib_dir" option.');
    }

    // initialize symfony core autoloading
    require_once($this->options['symfony_lib_dir'].'/autoload/sfCoreAutoload.class.php');
    sfCoreAutoload::getInstance()->register();

    // application
    $this->setName('symfony');
    $this->setVersion(SYMFONY_VERSION);

    $this->initializeEnvironment($this->options['symfony_lib_dir']);
    $this->initializeTasks();
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
   * Initializes the environment variables and include path.
   *
   * @param string The symfony lib directory
   */
  protected function initializeEnvironment($symfonyLibDir)
  {
    sfConfig::set('sf_symfony_lib_dir', $symfonyLibDir);

    // directory layout
    sfCore::initDirectoryLayout(getcwd());

    // include path
    set_include_path(
      sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_model_dir').PATH_SEPARATOR.
      get_include_path()
    );
  }

  /**
   * Loads all available tasks.
   *
   * Looks for tasks in the symfony core, the current project and all project plugins.
   */
  protected function initializeTasks()
  {
    $dirs = array(
      sfConfig::get('sf_symfony_lib_dir').'/task',                // symfony tasks
      sfConfig::get('sf_symfony_lib_dir').'/plugins/*/lib/task',  // bundled plugin tasks
      sfConfig::get('sf_plugins_dir').'/*/lib/task',              // plugin tasks
      sfConfig::get('sf_lib_dir').'/task',                        // project tasks
    );
    $finder = sfFinder::type('file')->name('*Task.class.php');
    foreach ($dirs as $globDir)
    {
      if (!$dirs = glob($globDir))
      {
        continue;
      }

      foreach ($finder->in($dirs) as $task)
      {
        require_once $task;
      }
    }

    foreach (get_declared_classes() as $class)
    {
      $r = new Reflectionclass($class);
      if ($r->isSubclassOf('sfTask') && !$r->isAbstract())
      {
        $this->registerTask(new $class($this->dispatcher, $this->formatter));
      }
    }
  }
}
