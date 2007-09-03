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
class sfSymfonyCommandApplication extends sfCommandApplication
{
  protected
    $autoloader = null;

  /**
   * Initializes the current symfony command application.
   *
   * @param string The symfony lib directory
   * @param string The symfony data directory
   */
  public function initialize($symfonyLibDir, $symfonyDataDir)
  {
    require_once($symfonyLibDir.'/util/sfCore.class.php');
    require_once($symfonyLibDir.'/config/sfConfig.class.php');
    require_once($symfonyLibDir.'/util/sfSimpleAutoload.class.php');

    // application
    $this->setName('symfony');
    $this->setVersion(sfCore::VERSION);

    $this->initializeEnvironment($symfonyLibDir, $symfonyDataDir);
    $this->initializeAutoloader();
    $this->initializeLogger();
    $this->initializeTasks();
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
      sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
      get_include_path()
    );
  }

  /**
   * Initializes the logger object.
   */
  protected function initializeLogger()
  {
    $logger = new sfCommandLogger(new sfEventDispatcher(), array('output' => new sfConsoleColorizer()));
    $this->setLogger($logger);
  }

  /**
   * Initializes the autoloader object.
   */
  protected function initializeAutoloader()
  {
    if (is_dir(sfConfig::get('sf_base_cache_dir')))
    {
      $cache = sfConfig::get('sf_base_cache_dir').DIRECTORY_SEPARATOR.'autoload_cmd.data';
    }
    else
    {
      require_once(sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php');
      $cache = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_cmd_%s.data', md5(__FILE__));
    }

    $this->autoloader = new sfSimpleAutoload($cache);
    $this->autoloader->addDirectory(sfConfig::get('sf_symfony_lib_dir'));
    $this->autoloader->addDirectory(sfConfig::get('sf_symfony_lib_dir').'/vendor/propel');
    $this->autoloader->addDirectory(sfConfig::get('sf_symfony_lib_dir').'/vendor/creole');
    $this->autoloader->addDirectory('lib/model');
    $this->autoloader->addDirectory('plugins');
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
      sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'tasks',        // project tasks
      sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'task', // symfony tasks
      sfConfig::get('sf_root_dir').'/plugins/*/lib/tasks',            // plugin tasks
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
        require_once($task);
      }
    }

    foreach (get_declared_classes() as $class)
    {
      $r = new Reflectionclass($class);
      if ($r->isSubclassOf('sfTask') && !$r->isAbstract())
      {
        $this->registerTask(new $class($this, $this->getLogger()));
      }
    }
  }
}
