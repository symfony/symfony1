<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProjectConfiguration represents a configuration for a symfony project.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectConfiguration
{
  protected
    $rootDir       = null,
    $symfonyLibDir = null;

  static protected
    $active = null;

  /**
   * Constructor.
   *
   * @param string              $rootDir    The project root directory
   * @param sfEventDispatcher   $dispatcher The event dispatcher
   */
  public function __construct($rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    if (is_null(sfProjectConfiguration::$active) || $this instanceof sfApplicationConfiguration)
    {
      sfProjectConfiguration::$active = $this;
    }

    $this->rootDir = is_null($rootDir) ? self::guessRootDir() : realpath($rootDir);

    $this->symfonyLibDir = realpath(dirname(__FILE__).'/..');

    // initializes autoloading for symfony core classes
    require_once $this->symfonyLibDir.'/autoload/sfCoreAutoload.class.php';
    sfCoreAutoload::register();

    $this->dispatcher = is_null($dispatcher) ? new sfEventDispatcher() : $dispatcher;

    ini_set('magic_quotes_runtime', 'off');
    ini_set('register_globals', 'off');

    sfConfig::set('sf_symfony_lib_dir', $this->symfonyLibDir);

    $this->setRootDir($this->rootDir);

    $this->setup();
  }

  /**
   * Setups the current configuration.
   *
   * Override this method if you want to customize your project configuration.
   */
  public function setup()
  {
  }

  /**
   * Sets the project root directory.
   *
   * @param string $rootDir The project root directory
   */
  public function setRootDir($rootDir)
  {
    $this->rootDir = $rootDir;

    sfConfig::add(array(
      'sf_root_dir' => $rootDir,

      // global directory structure
      'sf_apps_dir'    => $rootDir.DIRECTORY_SEPARATOR.'apps',
      'sf_lib_dir'     => $rootDir.DIRECTORY_SEPARATOR.'lib',
      'sf_log_dir'     => $rootDir.DIRECTORY_SEPARATOR.'log',
      'sf_data_dir'    => $rootDir.DIRECTORY_SEPARATOR.'data',
      'sf_config_dir'  => $rootDir.DIRECTORY_SEPARATOR.'config',
      'sf_test_dir'    => $rootDir.DIRECTORY_SEPARATOR.'test',
      'sf_doc_dir'     => $rootDir.DIRECTORY_SEPARATOR.'doc',
      'sf_plugins_dir' => $rootDir.DIRECTORY_SEPARATOR.'plugins',
    ));

    $this->setWebDir($rootDir.DIRECTORY_SEPARATOR.'web');
    $this->setCacheDir($rootDir.DIRECTORY_SEPARATOR.'cache');
  }

  /**
   * Returns the project root directory.
   *
   * @return string The project root directory
   */
  public function getRootDir()
  {
    return $this->rootDir;
  }

  /**
   * Sets the cache root directory.
   *
   * @param string $cacheDir The absolute path to the cache dir.
   */
  public function setCacheDir($cacheDir)
  {
    sfConfig::set('sf_cache_dir', $cacheDir);
  }

  /**
   * Sets the log directory.
   *
   * @param string $logDir The absolute path to the log dir.
   */
  public function setLogDir($logDir)
  {
    sfConfig::set('sf_log_dir', $logDir);
  }

  /**
   * Sets the web root directory.
   *
   * @param string $webDir The absolute path to the web dir.
   */
  public function setWebDir($webDir)
  {
    sfConfig::add(array(
      'sf_web_dir'    => $webDir,
      'sf_upload_dir' => $webDir.DIRECTORY_SEPARATOR.'uploads',
    ));
  }

  /**
   * Gets directories where model classes are stored.
   *
   * @return array An array of directories
   */
  public function getModelDirs()
  {
    $dirs = array(sfConfig::get('sf_lib_dir').'/model');                     // project
    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/lib/model'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                               // plugins
    }

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string $class  The generator class name
   * @param string $theme  The theme name
   *
   * @return array An array of directories
   */
  public function getGeneratorTemplateDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/template');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/data/generator/'.$class.'/'.$theme.'/template'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').'/plugins/*/data/generator/'.$class.'/'.$theme.'/template'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                         // bundled plugin
    }

    return $dirs;
  }

  /**
   * Gets directories where the skeleton is stored for a generator class and a specific theme.
   *
   * @param string $class   The generator class name
   * @param string $theme   The theme name
   *
   * @return array An array of directories
   */
  public function getGeneratorSkeletonDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/skeleton');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/data/generator/'.$class.'/'.$theme.'/skeleton'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').'/plugins/*/data/generator/'.$class.'/'.$theme.'/skeleton'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                         // bundled plugin
    }

    return $dirs;
  }

  /**
   * Gets the template to use for a generator class.
   *
   * @param string $class   The generator class name
   * @param string $theme   The theme name
   * @param string $path    The template path
   *
   * @return string A template path
   *
   * @throws sfException
   */
  public function getGeneratorTemplate($class, $theme, $path)
  {
    $dirs = $this->getGeneratorTemplateDirs($class, $theme);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.'/'.$path))
      {
        return $dir.'/'.$path;
      }
    }

    throw new sfException(sprintf('Unable to load "%s" generator template in: %s.', $path, implode(', ', $dirs)));
  }

  /**
   * Gets the paths to plugins root directories, minding overloaded plugins.
   *
   * @return array The plugin root paths.
   */
  public function getPluginPaths()
  {
    $pluginPaths = array();

    $finder = sfFinder::type('dir')->maxdepth(0)->follow_link()->relative();

    $bundledPlugins = $finder->discard('.*')->prune('.*')->in(sfConfig::get('sf_symfony_lib_dir').'/plugins');
    $projectPlugins = $finder->discard('.*')->prune('.*')->in(sfConfig::get('sf_plugins_dir'));

    // bundled plugins
    foreach ($bundledPlugins as $plugin)
    {
      // plugins can override bundle plugins
      if (false !== $pos = array_search($plugin, $projectPlugins))
      {
        $pluginPaths[] = sfConfig::get('sf_plugins_dir').'/'.$plugin;
        unset($projectPlugins[$pos]);
      }
      else
      {
        $pluginPaths[] = sfConfig::get('sf_symfony_lib_dir').'/plugins/'.$plugin;
      }
    }

    // project plugins
    foreach ($projectPlugins as $plugin)
    {
      $pluginPaths[] = sfConfig::get('sf_plugins_dir').'/'.$plugin;
    }

    return $pluginPaths;
  }

  /**
   * Returns the event dispatcher.
   *
   * @return sfEventDispatcher A sfEventDispatcher instance
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Returns the symfony lib directory.
   *
   * @return string The symfony lib directory
   */
  public function getSymfonyLibDir()
  {
    return $this->symfonyLibDir;
  }

  /**
   * Returns the active configuration.
   *
   * @return sfProjectConfiguration The current sfProjectConfiguration instance
   */
  static public function getActive()
  {
    return sfProjectConfiguration::$active;
  }

  static public function guessRootDir()
  {
    $r = new ReflectionClass('ProjectConfiguration');

    return realpath(dirname($r->getFileName()).'/..');
  }

  /**
   * Returns a sfApplicationConfiguration configuration for a given application.
   *
   * @param string            $application    An application name
   * @param string            $environment    The environment name
   * @param Boolean           $debug          true to enable debug mode
   * @param string            $rootDir        The project root directory
   * @param sfEventDispatcher $dispatcher     An event dispatcher
   *
   * @return sfApplicationConfiguration A sfApplicationConfiguration instance
   */
  static public function getApplicationConfiguration($application, $environment, $debug, $rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    $class = $application.'Configuration';

    if (is_null($rootDir))
    {
      $rootDir = self::guessRootDir();
    }

    if (!file_exists($file = $rootDir.'/apps/'.$application.'/config/'.$class.'.class.php'))
    {
      throw new InvalidArgumentException(sprintf('The application "%s" does not exist.', $application));
    }

    require_once $file;

    return new $class($environment, $debug, $rootDir, $dispatcher);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'configuration.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}
