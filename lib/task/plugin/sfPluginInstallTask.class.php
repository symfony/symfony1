<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Installs a plugin.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPluginInstallTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->aliases = array('plugin-install');
    $this->namespace = 'plugin';
    $this->name = 'install';

    $this->briefDescription = 'Installs a plugin';

    $this->detailedDescription = <<<EOF
The [plugin:install|INFO] task installs a plugin:

  [./symfony plugin:install http://plugins.symfony-project.com/sfGuargPlugin|INFO]

You can also install a local plugin archive by giving the path instead of
an URL:

  [./symfony plugin:install /Users/fabien/plugins/sfGuargPlugin-1.0.0.tgz|INFO]

If the plugin contains some web content (images, stylesheets or javascripts),
the task creates a [%name%|COMMENT] symbolic link for those assets under [web/|COMMENT].
On Windows, the task copy all the files to the [web/%name%|COMMENT] directory.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $packages = array($arguments['name']);
    $this->log($this->formatSection('plugin', sprintf('installing plugin "%s"', $arguments['name'])));
    list($ret, $error) = $this->pearRunCommand('install', array(), $packages);

    if ($error)
    {
      throw new sfCommandException($error);
    }

    $this->installWebContent($arguments['name']);
  }
}
