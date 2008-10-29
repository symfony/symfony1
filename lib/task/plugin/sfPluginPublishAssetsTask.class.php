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
 * Publishes Web Assets for Core and third party plugins
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabian Lange <fabian.lange@symfony-project.com>
 * @version    SVN: $Id: sfPluginPublishAssetsTask.class.php 7655 2008-02-28 09:52:40Z fabien $
 */
class sfPluginPublishAssetsTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('only-core', sfCommandArgument::OPTIONAL, 'if set only core plugins will pubish their assets'),
    ));
    
    $this->aliases = array();
    $this->namespace = 'plugin';
    $this->name = 'publish-assets';

    $this->briefDescription = 'Publishes web assets for all plugins';

    $this->detailedDescription = <<<EOF
The [plugin:publish-assets|INFO] task will publish web assets from all plugins.

  [./symfony plugin:publish-assets|INFO]

In fact this will send the [plugin.post_install|INFO] event to each plugin.

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
      $this->dispatcher->notify(new sfEvent($this, 'plugin.post_install', array('plugin' => $plugin, 'plugin_dir' => $corePluginsDir)));
    }
    if (! $arguments['only-core']) {
      $thirdPartyPlugins = sfConfig::get('sf_plugins_dir');
      foreach (sfFinder::type('dir')->relative()->maxdepth(0)->in($thirdPartyPlugins) as $plugin)
      {
        $this->logSection('plugin', 'Configuring plugin - '.$plugin);
        $this->dispatcher->notify(new sfEvent($this, 'plugin.post_install', array('plugin' => $plugin, 'plugin_dir' => $thirdPartyPlugins)));
      }
    }
  }
}