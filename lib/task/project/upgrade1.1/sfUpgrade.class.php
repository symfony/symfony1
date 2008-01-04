<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for upgrade classes.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfUpgrade extends sfTask
{
  protected
    $task = null;

  /**
   * Upgrades the current project from 1.0 to 1.1.
   */
  abstract public function upgrade();

  public function execute($arguments = array(), $options = array())
  {
    throw new sfException('You can\'t execute this task.');
  }

  /**
   * Returns a finder that exclude upgrade scripts from being upgraded!
   *
   * @param  string   String directory or file or any (for both file and directory)
   *
   * @return sfFinder A sfFinder instance
   */
  protected function getFinder($type)
  {
    return sfFinder::type($type)->prune('upgrade1.1');
  }

  /**
   * Returns all project directories where you can put PHP classes.
   */
  protected function getProjectClassDirectories()
  {
    return array_merge(
      $this->getProjectLibDirectories(),
      $this->getProjectActionDirectories()
    );
  }

  /**
   * Returns all project directories where you can put templates.
   */
  protected function getProjectTemplateDirectories()
  {
    return array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/templates'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/templates')
    );
  }

  /**
   * Returns all project directories where you can put actions and components.
   */
  protected function getProjectActionDirectories()
  {
    return glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/actions');
  }

  /**
   * Returns all project lib directories.
   */
  protected function getProjectLibDirectories()
  {
    return array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/lib'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/lib'),
      array(
        sfConfig::get('sf_root_dir').'/apps/lib',
        sfConfig::get('sf_root_dir').'/lib',
      )
    );
  }

  /**
   * Returns all project config directories.
   */
  protected function getProjectConfigDirectories()
  {
    return array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/config'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/config'),
      glob(sfConfig::get('sf_root_dir').'/config')
    );
  }

  /**
   * Forward all non existing methods to the task.
   *
   * @param  string The method name
   * @param  array  An array of arguments
   *
   * @return mixed  The return value of the task method call
   */
  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->task, $method), $arguments);
  }
}
