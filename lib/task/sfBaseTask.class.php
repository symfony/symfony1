<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all symfony tasks.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfBaseTask extends sfCommandApplicationTask
{
  protected
    $configuration = null;

  /**
   * @see sfTask
   */
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

    $this->checkProjectExists();

    $application = null;
    if ($commandManager->getArgumentSet()->hasArgument('application'))
    {
      $application = $commandManager->getArgumentValue('application');
    }
    else if ($commandManager->getOptionSet()->hasOption('application'))
    {
      $application = $commandManager->getOptionValue('application');
    }

    $env = $commandManager->getOptionSet()->hasOption('env') ? $commandManager->getOptionValue('env') : 'test';

    if (!is_null($application))
    {
      $this->checkAppExists($application);

      require_once sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php';

      $isDebug = $commandManager->getOptionSet()->hasOption('debug') ? $commandManager->getOptionValue('debug') : true;
      $this->configuration = ProjectConfiguration::getApplicationConfiguration($application, $env, $isDebug, null, $this->dispatcher);
    }
    else
    {
      if (file_exists(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php'))
      {
        require_once sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php';
        $this->configuration = new ProjectConfiguration(null, $this->dispatcher);
      }
      else
      {
        $this->configuration = new sfProjectConfiguration(getcwd(), $this->dispatcher);
      }

      if (!is_null($env))
      {
        sfConfig::set('sf_environment', $env);
      }
    }

    $autoloader = sfSimpleAutoload::getInstance();
    foreach ($this->configuration->getModelDirs() as $dir)
    {
      $autoloader->addDirectory($dir);
    }

    if (!is_null($this->commandApplication) && !$this->commandApplication->withTrace())
    {
      sfConfig::set('sf_logging_enabled', false);
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent($this, 'command.post_command'));

    return $ret;
  }

  /**
   * Returns the filesystem instance.
   *
   * @return sfFilesystem A sfFilesystem instance
   */
  public function getFilesystem()
  {
    if (!isset($this->filesystem))
    {
      if (is_null($this->commandApplication) || $this->commandApplication->isVerbose())
      {
        $this->filesystem = new sfFilesystem($this->dispatcher, $this->formatter);
      }
      else
      {
        $this->filesystem = new sfFilesystem();
      }
    }

    return $this->filesystem;
  }

  /**
   * Checks if the current directory is a symfony project directory.
   *
   * @return true if the current directory is a symfony project directory, false otherwise
   */
  public function checkProjectExists()
  {
    if (!file_exists('symfony'))
    {
      throw new sfException('You must be in a symfony project directory.');
    }
  }

  /**
   * Checks if an application exists.
   *
   * @param  string $app  The application name
   *
   * @return bool true if the application exists, false otherwise
   */
  public function checkAppExists($app)
  {
    if (!is_dir(sfConfig::get('sf_apps_dir').'/'.$app))
    {
      throw new sfException(sprintf('Application "%s" does not exist', $app));
    }
  }

  /**
   * Checks if a module exists.
   *
   * @param  string $app     The application name
   * @param  string $module  The module name
   *
   * @return bool true if the module exists, false otherwise
   */
  public function checkModuleExists($app, $module)
  {
    if (!is_dir(sfConfig::get('sf_apps_dir').'/'.$app.'/modules/'.$module))
    {
      throw new sfException(sprintf('Module "%s/%s" does not exist.', $app, $module));
    }
  }
}
