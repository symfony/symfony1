<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uninstall a plugin.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPluginUninstallTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->aliases = array('plugin-uninstall');
    $this->namespace = 'plugin';
    $this->name = 'uninstall';

    $this->briefDescription = 'Uninstalls a plugin';

    $this->detailedDescription = <<<EOF
The [plugin:uninstall|INFO] task uninstalls a plugin:

  [./symfony plugin:uninstall symfony/sfGuargPlugin|INFO]

If the plugin contains some web content (images, stylesheets or javascripts),
the task also removes the [web/%name%|COMMENT] symbolic link (on *nix)
or directory (on Windows).
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->uninstallWebContent($arguments['name']);

    $packages = array($arguments['name']);
    $this->log($this->formatSection('plugin', sprintf('uninstalling plugin "%s"', $arguments['name'])));
    list($ret, $error) = $this->pearRunCommand('uninstall', array(), $packages);

    if ($error)
    {
      throw new sfCommandException($error);
    }
  }
}
