<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfiguration represents a configuration for a symfony application.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfApplicationConfiguration extends ProjectConfiguration
{
  protected
    $configCache = null,
    $application = null,
    $environment = null,
    $debug       = false;

  /**
   * Constructor.
   *
   * @param string  The environment name
   * @param Boolean true to enable debug mode
   * @param string  The project root directory
   */
  public function __construct($environment, $debug, $rootDir = null)
  {
    $this->environment = $environment;
    $this->debug       = (boolean) $debug;
    $this->application = str_replace('Configuration', '', get_class($this));

    parent::__construct($rootDir);

    $this->configure();

    $this->initConfiguration();

    if (sfConfig::get('sf_check_lock'))
    {
      $this->checkLock();
    }

    if (sfConfig::get('sf_check_symfony_version'))
    {
      $this->checkSymfonyVersion();
    }

    $this->initialize();
  }

  /**
   * Configures the current configuration.
   *
   * Override this method if you want to customize your application configuration.
   */
  public function configure()
  {
  }

  /**
   * Initialized the current configuration.
   *
   * Override this method if you want to customize your application initialization.
   */
  public function initialize()
  {
  }

  /**
   * @see sfProjectConfiguration
   */
  public function initConfiguration()
  {
    // in debug mode, start global timer
    if ($this->isDebug())
    {
      sfConfig::set('sf_timer_start', microtime(true));
    }

    $configCache = $this->getConfigCache();

    // required core classes for the framework
    if (!sfConfig::get('sf_debug') && !sfConfig::get('sf_test'))
    {
      $configCache->import('config/core_compile.yml', false);
    }

    sfAutoload::getInstance()->register();

    // load base settings
    include($configCache->checkConfig('config/settings.yml'));
    if ($file = $configCache->checkConfig('config/app.yml', true))
    {
      include($file);
    }

    if (false !== sfConfig::get('sf_csrf_secret'))
    {
      sfForm::enableCSRFProtection(sfConfig::get('sf_csrf_secret'));
    }

    // force setting default timezone if not set
    if ($default_timezone = sfConfig::get('sf_default_timezone'))
    {
      date_default_timezone_set($default_timezone);
    }
    else if (sfConfig::get('sf_force_default_timezone', true))
    {
      date_default_timezone_set(@date_default_timezone_get());
    }

    // error settings
    ini_set('display_errors', $this->isDebug() ? 'on' : 'off');
    error_reporting(sfConfig::get('sf_error_reporting'));

    // include all config.php from plugins
    $this->loadPluginConfig();

    if ($this->isDebug())
    {
      spl_autoload_register(array(sfAutoload::getInstance(), 'autoloadAgain'));
    }

    // compress output
    ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : '');
  }

  /**
   * Returns a configuration cache object for the current configuration.
   *
   * @return sfConfigCache A sfConfigCache instance
   */
  public function getConfigCache()
  {
    if (is_null($this->configCache))
    {
      $this->configCache = new sfConfigCache($this);
    }

    return $this->configCache;
  }

  /**
   * Check lock files to see if we're not in a cache cleaning process.
   *
   * @return void
   */
  public function checkLock()
  {
    if (sfToolkit::hasLockFile(sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.$this->getApplication().'_'.$this->getEnvironment().'.lck', 5))
    {
      // application is not available - we'll find the most specific unavailable page...
      $files[] = sfConfig::get('sf_apps_dir').'/'.$this->getApplication().'/config/unavailable.php';
      $files[] = sfConfig::get('sf_root_dir').'/config/unavailable.php';
      $files[] = sfConfig::get('sf_web_dir').'/errors/unavailable.php';

      // symfony default
      $files[] = sfConfig::get('sf_symfony_lib_dir').'/exception/data/unavailable.php';

      foreach($files as &$file)
      {
        if (is_readable($file))
        {
          include $file;
        }
      }

      die(1);
    }
  }

  /**
   * Checks symfony version and clears cache if recent update.
   *
   * @return void
   */
  public function checkSymfonyVersion()
  {
    // recent symfony update?
    if (SYMFONY_VERSION != @file_get_contents(sfConfig::get('sf_config_cache_dir').'/VERSION'))
    {
      // clear cache
      sfToolkit::clearDirectory(sfConfig::get('sf_config_cache_dir'));
    }
  }

  /**
   * Sets the project root directory.
   *
   * @param string The project root directory
   */
  public function setRootDir($rootDir)
  {
    parent::setRootDir($rootDir);

    sfConfig::add(array(
      'sf_app'         => $this->getApplication(),
      'sf_environment' => $this->getEnvironment(),
      'sf_debug'       => $this->isDebug(),
    ));

    $this->setAppDir(sfConfig::get('sf_apps_dir').DIRECTORY_SEPARATOR.$this->getApplication());
  }

  /**
   * Sets the app directory.
   *
   * @param string The absolute path to the app dir.
   */
  public function setAppDir($appDir)
  {
    sfConfig::add(array(
      'sf_app_dir' => $appDir,

      // SF_APP_DIR directory structure
      'sf_app_config_dir'   => $appDir.DIRECTORY_SEPARATOR.'config',
      'sf_app_lib_dir'      => $appDir.DIRECTORY_SEPARATOR.'lib',
      'sf_app_module_dir'   => $appDir.DIRECTORY_SEPARATOR.'modules',
      'sf_app_template_dir' => $appDir.DIRECTORY_SEPARATOR.'templates',
      'sf_app_i18n_dir'     => $appDir.DIRECTORY_SEPARATOR.'i18n',
    ));
  }

  /**
   * @see sfProjectConfiguration
   */
  public function setCacheDir($cacheDir)
  {
    parent::setCacheDir($cacheDir);

    sfConfig::add(array(
      'sf_app_base_cache_dir' => $cacheDir.DIRECTORY_SEPARATOR.$this->getApplication(),
      'sf_app_cache_dir'      => $appCacheDir = $cacheDir.DIRECTORY_SEPARATOR.$this->getApplication().DIRECTORY_SEPARATOR.$this->getEnvironment(),

      // SF_CACHE_DIR directory structure
      'sf_template_cache_dir' => $appCacheDir.DIRECTORY_SEPARATOR.'template',
      'sf_i18n_cache_dir'     => $appCacheDir.DIRECTORY_SEPARATOR.'i18n',
      'sf_config_cache_dir'   => $appCacheDir.DIRECTORY_SEPARATOR.'config',
      'sf_test_cache_dir'     => $appCacheDir.DIRECTORY_SEPARATOR.'test',
      'sf_module_cache_dir'   => $appCacheDir.DIRECTORY_SEPARATOR.'modules',
    ));
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  public function getControllerDirs($moduleName)
  {
    $dirs = array();
    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[$key.'/'.$moduleName.'/actions'] = $value;
    }

    $dirs[sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/actions'] = false;                                     // application

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$moduleName.'/actions'))
    {
      $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), true))); // plugins
    }

    $dirs[sfConfig::get('sf_symfony_lib_dir').'/controller/'.$moduleName.'/actions'] = true;                          // core modules

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  public function getTemplateDirs($moduleName)
  {
    $dirs = array();
    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[] = $key.'/'.$moduleName.'/templates';
    }

    $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';                        // application

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$moduleName.'/templates'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                        // plugins
    }

    $dirs[] = sfConfig::get('sf_symfony_lib_dir').'/controller/'.$moduleName.'/templates';            // core modules
    $dirs[] = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($moduleName.'/templates');         // generated templates in cache

    return $dirs;
  }

  /**
   * Gets the template directory to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template directory
   */
  public function getTemplateDir($moduleName, $templateFile)
  {
    foreach ($this->getTemplateDirs($moduleName) as $dir)
    {
      if (is_readable($dir.'/'.$templateFile))
      {
        return $dir;
      }
    }

    return null;
  }

  /**
   * Gets the template to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template path
   */
  public function getTemplatePath($moduleName, $templateFile)
  {
    $dir = $this->getTemplateDir($moduleName, $templateFile);

    return $dir ? $dir.'/'.$templateFile : null;
  }

  /**
   * Gets the decorator directories.
   *
   * @param  string The template file
   *
   * @return array  An array of the decorator directories
   *
   */
  public function getDecoratorDirs()
  {
    return array(sfConfig::get('sf_app_template_dir'));
  }

  /**
   * Gets the decorator directory for a given template.
   *
   * @param  string The template file
   *
   * @return string A template directory
   */
  public function getDecoratorDir($template)
  {
    foreach ($this->getDecoratorDirs() as $dir)
    {
      if (is_readable($dir.'/'.$template))
      {
        return $dir;
      }
    }
  }

  /**
   * Gets the i18n directories to use globally.
   *
   * @return array An array of i18n directories
   */
  public function getI18NGlobalDirs()
  {
    $dirs = array();

    // application
    if (is_dir($dir = sfConfig::get('sf_app_dir').'/i18n'))
    {
      $dirs[] = $dir;
    }

    // plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/i18n');
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    return $dirs;
  }

  /**
   * Gets the i18n directories to use for a given module.
   *
   * @param string The module name
   *
   * @return array An array of i18n directories
   */
  public function getI18NDirs($moduleName)
  {
    $dirs = array();

    // module
    if (is_dir($dir = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/i18n'))
    {
      $dirs[] = $dir;
    }

    // application
    if (is_dir($dir = sfConfig::get('sf_app_dir').'/i18n'))
    {
      $dirs[] = $dir;
    }

    // module in plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$moduleName.'/i18n');
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    // plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/i18n');
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    return $dirs;
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string The configuration path
   *
   * @return array An array of paths
   */
  public function getConfigPaths($configPath)
  {
    $globalConfigPath = basename(dirname($configPath)).'/'.basename($configPath);

    $files = array(
      sfConfig::get('sf_symfony_lib_dir').'/config/'.$globalConfigPath,              // symfony
    );

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').'/plugins/*/'.$globalConfigPath))
    {
      $files = array_merge($files, $bundledPluginDirs);                              // bundled plugins
    }

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/'.$globalConfigPath))
    {
      $files = array_merge($files, $pluginDirs);                                     // plugins
    }

    $files = array_merge($files, array(
      sfConfig::get('sf_root_dir').'/'.$globalConfigPath,                            // project
      sfConfig::get('sf_root_dir').'/'.$configPath,                                  // project
      sfConfig::get('sf_app_dir').'/'.$globalConfigPath,                             // application
      sfConfig::get('sf_app_cache_dir').'/'.$configPath,                             // generated modules
    ));

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/'.$configPath))
    {
      $files = array_merge($files, $pluginDirs);                                     // plugins
    }

    $files[] = sfConfig::get('sf_app_dir').'/'.$configPath;                          // module

    $configs = array();
    foreach (array_unique($files) as $file)
    {
      if (is_readable($file))
      {
        $configs[] = $file;
      }
    }

    return $configs;
  }

  /**
   * Loads config.php files from plugins
   *
   * @return void
   */
  public function loadPluginConfig()
  {
    if ($pluginConfigs = glob(sfConfig::get('sf_symfony_lib_dir').'/plugins/*/config/config.php'))
    {
      foreach ($pluginConfigs as $config)
      {
        require_once($config);
      }
    }

    if ($pluginConfigs = glob(sfConfig::get('sf_plugins_dir').'/*/config/config.php'))
    {
      foreach ($pluginConfigs as $config)
      {
        require_once($config);
      }
    }
  }

  /**
   * Returns the application name.
   *
   * @return string The application name
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Returns the environment name.
   *
   * @return string The environment name
   */
  public function getEnvironment()
  {
    return $this->environment;
  }

  /**
   * Returns true if this configuration has debug enabled.
   *
   * @return Boolean true if the configuration has debug enabled, false otherwise
   */
  public function isDebug()
  {
    return $this->debug;
  }
}
