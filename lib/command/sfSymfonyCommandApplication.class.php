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
  protected
    $autoloader = null;

  /**
   * Configures the current symfony command application.
   *
   * @param string The symfony lib directory
   * @param string The symfony data directory
   */
  public function configure()
  {
    if (!isset($this->options['symfony_lib_dir']))
    {
      throw new sfInitializationException('You must pass a "symfony_lib_dir" option.');
    }

    if (!isset($this->options['symfony_data_dir']))
    {
      throw new sfInitializationException('You must pass a "symfony_data_dir" option.');
    }

    require_once($this->options['symfony_lib_dir'].'/util/sfCore.class.php');
    require_once($this->options['symfony_lib_dir'].'/config/sfConfig.class.php');
    require_once($this->options['symfony_lib_dir'].'/util/sfSimpleAutoload.class.php');

    // application
    $this->setName('symfony');
    $this->setVersion(sfCore::VERSION);

    $this->initializeEnvironment($this->options['symfony_lib_dir'], $this->options['symfony_data_dir']);
    $this->initializeAutoloader();
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
   * Returns the autoloader object.
   *
   * @param object The autoloader object
   */
  public function getAutoloader()
  {
    return $this->autoloader;
  }

  /**
   * Initializes the environment variables and include path.
   *
   * @param string The symfony lib directory
   * @param string The symfony data directory
   */
  protected function initializeEnvironment($symfonyLibDir, $symfonyDataDir)
  {
    sfConfig::add(array(
      'sf_symfony_lib_dir'  => $symfonyLibDir,
      'sf_symfony_data_dir' => $symfonyDataDir,
    ));

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
   * Initializes the autoloader object.
   *
   * If we are not using the symfony CLI in the context of a specific application,
   * then the system temp directory will be used for the autoloader cache instead.
   */
  protected function initializeAutoloader()
  {
    if (is_dir(sfConfig::get('sf_app_base_cache_dir')))
    {
      $cache = sfConfig::get('sf_app_base_cache_dir').DIRECTORY_SEPARATOR.'autoload_cmd.data';
    }
    else
    {
      require_once(sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php');
      $cache = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_cmd_%s.data', md5(__FILE__));
      die($cache);
    }

    $this->autoloader = sfSimpleAutoload::getInstance($cache);
    require_once(sfConfig::get('sf_symfony_lib_dir').'/util/sfFinder.class.php');
    $finder = sfFinder::type('file')->ignore_version_control()->prune('test')->name('*.php');
    $this->autoloader->addFiles($finder->in(sfConfig::get('sf_symfony_lib_dir')));
    $this->autoloader->addDirectory(sfConfig::get('sf_root_dir').'/plugins');
    $this->autoloader->register();
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
      sfConfig::get('sf_symfony_lib_dir').'/plugins/*/lib/tasks', // bundled plugin tasks
      sfConfig::get('sf_root_dir').'/plugins/*/lib/tasks',        // plugin tasks
      sfConfig::get('sf_lib_dir').'/tasks',                       // project tasks
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
