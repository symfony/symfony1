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
    $this->process($commandManager, $options);

    $this->checkProjectExists();

    $application = $commandManager->getArgumentSet()->hasArgument('application') ? $commandManager->getArgumentValue('application') : null;
    if (!is_null($application))
    {
      $this->checkAppExists($application);
      $class = $application.'Configuration';
      require_once sfConfig::get('sf_lib_dir').'/'.$class.'.class.php';
      $this->configuration = new $class('test', true);
    }
    else
    {
      if (file_exists(sfConfig::get('sf_lib_dir').'/ProjectConfiguration.class.php'))
      {
        require_once sfConfig::get('sf_lib_dir').'/ProjectConfiguration.class.php';
        $this->configuration = new ProjectConfiguration();
      }
      else
      {
        $this->configuration = new sfProjectConfiguration(getcwd());
      }
    }

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
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
   * @param string The application name
   *
   * @return true if the application exists, false otherwise
   */
  public function checkAppExists($app)
  {
    if (!is_dir(getcwd().'/apps/'.$app))
    {
      throw new sfException(sprintf('Application "%s" does not exist', $app));
    }
  }

  /**
   * Checks if a module exists.
   *
   * @param string The application name
   * @param string The module name
   *
   * @return true if the module exists, false otherwise
   */
  public function checkModuleExists($app, $module)
  {
    if (!is_dir(getcwd().'/apps/'.$app.'/modules/'.$module))
    {
      throw new sfException(sprintf('Module "%s/%s" does not exist.', $app, $module));
    }
  }
}
