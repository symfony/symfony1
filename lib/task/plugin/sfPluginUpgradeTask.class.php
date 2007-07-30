<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrades a plugin.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPluginUpgradeTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::OPTIONAL, 'The plugin name'),
    ));

    $this->aliases = array('plugin-upgrade');
    $this->namespace = 'plugin';
    $this->name = 'upgrade';

    $this->briefDescription = 'Upgrades a plugin';

    $this->detailedDescription = <<<EOF
The [plugin:upgrade|INFO] task tries to upgrade a plugin:

  [./symfony plugin:upgrade symfony/sfGuargPlugin|INFO]

If the plugin contains some web content (images, stylesheets or javascripts),
the task also updates the [web/%name%|COMMENT] directory content on Windows.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $config = $this->pearInit();

    $packages = array($arguments['name']);
    $this->log($this->formatSection('plugin', sprintf('upgrading plugin "%s"', $arguments['name'])));
    list($ret, $error) = $this->pearRunCommand($config, 'upgrade', array('loose' => true, 'nodeps' => true), $packages);

    if ($error)
    {
      throw new sfCommandException($error);
    }

    $name = $this->getPluginName($arguments['name']);
    $this->uninstallWebContent($name);
    $this->installWebContent($name);
  }
}
