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
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfBaseTask extends sfTask
{
  /**
   * @see sfTask
   */
  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->process($commandManager, $options);

    $this->checkProjectExists();

    try
    {
      if (!is_null($commandManager->getArgumentValue('application')))
      {
        $this->checkAppExists($commandManager->getArgumentValue('application'));
      }
    }
    catch (sfCommandException $e)
    {
    }

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
  }

  public function __get($key)
  {
    switch ($key)
    {
      case 'filesystem':
        if (!isset($this->filesystem))
        {
          $this->filesystem = new sfFilesystem($this->getLogger());
        }

        return $this->filesystem;
      default:
        return parent::__get($key);
    }
  }

  /**
   * Bootstraps a symfony application.
   *
   * @param string  The application name
   * @param string  The environment name
   * @param Boolean Whether to bootstrap the symfony application in debug mode
   */
  public function bootstrapSymfony($app, $env = 'dev', $debug = true)
  {
    define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
    define('SF_APP',         $app);
    define('SF_ENVIRONMENT', $env);
    define('SF_DEBUG',       $debug);

    require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

    sfContext::getInstance();
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
