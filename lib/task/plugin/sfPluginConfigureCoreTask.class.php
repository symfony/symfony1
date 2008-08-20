<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfPluginBaseTask.class.php');

/**
 * Lists installed plugins.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabian Lange <fabian.lange@symfony-project.com>
 * @version    SVN: $Id: sfPluginConfigureCoreTask.class.php 7655 2008-02-28 09:52:40Z fabien $
 */
class sfPluginConfigureCoreTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'plugin';
    $this->name = 'configure-core';

    $this->briefDescription = 'Configures symfony core plugins';

    $this->detailedDescription = <<<EOF
The [plugin:coreconfigure|INFO] task will configure core plugins for use:

  [./symfony plugin:configure-core|INFO]

In fact this will send the [plugin.post_install|INFO] event for each core plugin.

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    //we need the PluginManager to be listening for the event, so poke it
    $this->getPluginManager();
    
    $corePluginsDir = sfConfig::get('sf_symfony_lib_dir').'/plugins/';
    foreach (sfFinder::type('dir')->relative()->maxdepth(0)->in($corePluginsDir) as $plugin)
    {
      $this->logSection('plugin', 'Configuring core plugin - '.$plugin);
      $this->dispatcher->notify(new sfEvent($this, 'plugin.post_install', array('plugin' => $plugin, 'sourceDirectory' => $corePluginsDir)));
    }
  }
}