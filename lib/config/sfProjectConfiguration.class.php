<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfiguration represents a configuration for a symfony project.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectConfiguration
{
  protected
    $rootDir        = null,
    $symfonyLibDir  = null,
    $config         = array();

  static protected
    $active = null;

  /**
   * Constructor.
   */
  public function __construct($rootDir = null)
  {
    sfProjectConfiguration::$active = $this;

    $this->setRootDir($rootDir);

    $this->rootDir       = realpath($this->getRootDir());
    $this->symfonyLibDir = realpath(dirname(__FILE__).'/..');

    // initializes autoloading for symfony core classes
    require_once $this->symfonyLibDir.'/autoload/sfCoreAutoload.class.php';
    sfCoreAutoload::register();

    $this->dispatcher = new sfEventDispatcher();

    $this->initConfiguration();

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

  public function initConfiguration()
  {
    // directory layout
    sfConfig::add($this->getDirectoryStructure());

    ini_set('magic_quotes_runtime', 'off');
    ini_set('register_globals', 'off');
  }

  /**
   * Returns the directory structure for the current configuration.
   *
   * @return array An array containing the basic directory structure of the current configuration
   */
  public function getDirectoryStructure()
  {
    return array(
      'sf_symfony_lib_dir' => $this->getSymfonyLibDir(),
      'sf_root_dir'        => $sf_root_dir = $this->getRootDir(),

      // root directory names
      'sf_bin_dir_name'     => $sf_bin_dir_name     = 'batch',
      'sf_cache_dir_name'   => $sf_cache_dir_name   = 'cache',
      'sf_log_dir_name'     => $sf_log_dir_name     = 'log',
      'sf_lib_dir_name'     => $sf_lib_dir_name     = 'lib',
      'sf_web_dir_name'     => $sf_web_dir_name     = 'web',
      'sf_upload_dir_name'  => $sf_upload_dir_name  = 'uploads',
      'sf_data_dir_name'    => $sf_data_dir_name    = 'data',
      'sf_config_dir_name'  => $sf_config_dir_name  = 'config',
      'sf_apps_dir_name'    => $sf_apps_dir_name    = 'apps',
      'sf_test_dir_name'    => $sf_test_dir_name    = 'test',
      'sf_doc_dir_name'     => $sf_doc_dir_name     = 'doc',
      'sf_plugins_dir_name' => $sf_plugins_dir_name = 'plugins',

      // global directory structure
      'sf_apps_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_apps_dir_name,
      'sf_lib_dir'        => $sf_lib_dir = $sf_root_dir.DIRECTORY_SEPARATOR.$sf_lib_dir_name,
      'sf_bin_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_bin_dir_name,
      'sf_web_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_web_dir_name,
      'sf_upload_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_web_dir_name.DIRECTORY_SEPARATOR.$sf_upload_dir_name,
      'sf_log_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_log_dir_name,
      'sf_data_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_data_dir_name,
      'sf_config_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_config_dir_name,
      'sf_test_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_test_dir_name,
      'sf_doc_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$sf_doc_dir_name,
      'sf_plugins_dir'    => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_plugins_dir_name,
      'sf_cache_dir'      => $sf_cache_dir = $sf_root_dir.DIRECTORY_SEPARATOR.$sf_cache_dir_name,

      // lib directory names
      'sf_model_dir_name' => $sf_model_dir_name = 'model',

      // lib directory structure
      'sf_model_lib_dir'  => $sf_lib_dir.DIRECTORY_SEPARATOR.$sf_model_dir_name,

      // SF_APP_DIR sub-directories names
      'sf_app_i18n_dir_name'     => $sf_app_i18n_dir_name     = 'i18n',
      'sf_app_config_dir_name'   => $sf_app_config_dir_name   = 'config',
      'sf_app_lib_dir_name'      => $sf_app_lib_dir_name      = 'lib',
      'sf_app_module_dir_name'   => $sf_app_module_dir_name   = 'modules',
      'sf_app_template_dir_name' => $sf_app_template_dir_name = 'templates',

      // SF_APP_MODULE_DIR sub-directories names
      'sf_app_module_action_dir_name'   => 'actions',
      'sf_app_module_template_dir_name' => 'templates',
      'sf_app_module_lib_dir_name'      => 'lib',
      'sf_app_module_view_dir_name'     => 'views',
      'sf_app_module_validate_dir_name' => 'validate',
      'sf_app_module_config_dir_name'   => 'config',
      'sf_app_module_i18n_dir_name'     => 'i18n',
    );
  }

  /**
   * Gets directories where model classes are stored.
   *
   * @return array An array of directories
   */
  public function getModelDirs()
  {
    $dirs = array(sfConfig::get('sf_lib_dir').'/model' ? sfConfig::get('sf_lib_dir').'/model' : 'lib/model'); // project
    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/lib/model'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugins
    }

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
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
   * @param string The generator class name
   * @param string The theme name
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
   * @param string The generator class name
   * @param string The theme name
   * @param string The template path
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
   * Sets the project root directory.
   *
   * @param string The project root directory
   */
  public function setRootDir($rootDir)
  {
    $this->rootDir = $rootDir;
  }

  /**
   * Returns the project root directory.
   *
   * @return string The project root directory
   */
  public function getRootDir()
  {
    if (is_null($this->rootDir))
    {
      $r = new ReflectionObject($this);

      $this->rootDir = realpath(dirname($r->getFileName()).'/..');
    }

    return $this->rootDir;
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
}
