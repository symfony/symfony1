<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginConfiguration represents a configuration for a symfony plugin.
 * 
 * @package    symfony
 * @subpackage config
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfPluginConfiguration
{
  protected
    $configuration = null,
    $dispatcher    = null,
    $rootDir       = null;

  /**
   * Constructor.
   * 
   * @param sfProjectConfiguration  $configuration
   * @param string                  $rootDir        The plugin's root directory
   */
  public function __construct(sfProjectConfiguration $configuration, $rootDir = null)
  {
    $this->configuration = $configuration;
    $this->dispatcher = $configuration->getEventDispatcher();
    $this->rootDir = is_null($rootDir) ? self::guessRootDir() : realpath($rootDir);

    $this->setup();
    $this->configure();

    if (!$this->configuration instanceof sfApplicationConfiguration)
    {
      $this->initializeAutoload();
      $this->initialize();
    }
  }

  /**
   * Sets up the plugin.
   * 
   * This method can be used when creating a base plugin configuration class for other plugins to extend.
   */
  public function setup()
  {
  }

  /**
   * Configures the plugin.
   * 
   * This method is called before the plugin's classes have been added to sfAutoload.
   */
  public function configure()
  {
  }

  /**
   * Initializes the plugin.
   * 
   * This method is called after the plugin's classes have been added to sfAutoload.
   * 
   * @return boolean|null If false sfApplicationConfiguration will look for a config.php (maintains BC with symfony < 1.2)
   */
  public function initialize()
  {
  }

  /**
   * Returns the plugin's root directory.
   * 
   * @return string
   */
  public function getRootDir()
  {
    return $this->rootDir;
  }

  /**
   * Initializes autoloading for the plugin.
   * 
   * This method is called when a plugin is initialized in a project
   * configuration. Otherwise, autoload is handled (and cached) in
   * sfApplicationConfiguration.
   */
  public function initializeAutoload()
  {
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');

    if (is_readable($file = $this->rootDir.'/config/autoload.yml'))
    {
      $this->configuration->getEventDispatcher()->connect('autoload.filter_config', array($this, 'filterAutoloadConfig'));

      $config = new sfAutoloadConfigHandler();
      $mappings = $config->evaluate(array($file));

      foreach ($mappings as $class => $file)
      {
        $autoload->setClassPath($class, $file);
      }
    }
    else
    {
      $autoload->addDirectory($this->rootDir.'/lib');
    }

    $autoload->register();
  }

  /**
   * Filters sfAutoload configuration values.
   * 
   * @param   array $config
   * 
   * @return  array
   */
  public function filterAutoloadConfig(sfEvent $event, array $config)
  {
    $addLib = true;
    $addModuleLib = true;

    // if the plugin has an autoload.yml we need to play nice
    if (is_readable($this->rootDir.'/config/autoload.yml'))
    {
      foreach ($config['autoload'] as $name => $entry)
      {
        if (isset($entry['path']))
        {
          $dirs = glob($entry['path']);
          if (!is_array($dirs))
          {
            continue;
          }

          if (in_array($this->rootDir.'/lib', $dirs))
          {
            $addLib = false;
          }
          else
          {
            $moduleDirs = glob($this->rootDir.'/modules/*/lib');
            if (is_array($moduleDirs) && array_intersect($moduleDirs, $dirs))
            {
              $addModuleLib = false;
            }
          }
        }
      }
    }

    if ($addLib)
    {
      $config['autoload'][basename($this->rootDir).'_lib'] = array(
        'path'      => $this->rootDir.'/lib',
        'recursive' => true,
      );
    }

    if ($addModuleLib)
    {
      $config['autoload'][basename($this->rootDir).'_module_libs'] = array(
        'path'      => $this->rootDir.'/modules/*/lib',
        'recursive' => true,
        'prefix'    => 1,
      );
    }

    return $config;
  }

  /**
   * Guesses the plugin root directory.
   * 
   * @return string
   */
  static protected function guessRootDir()
  {
    return realpath(dirname(__FILE__).'/..');
  }
}
