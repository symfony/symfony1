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
 * Installs a plugin.
 *
 * @package    symfony
 * @subpackage task
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

    $this->addOptions(array(
      new sfCommandOption('stability', 's', sfCommandOption::PARAMETER_REQUIRED, 'The preferred stability (stable, beta, alpha)', null),
      new sfCommandOption('release', 'r', sfCommandOption::PARAMETER_REQUIRED, 'The preferred version', null),
      new sfCommandOption('channel', 'c', sfCommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null),
      new sfCommandOption('install_deps', 'd', sfCommandOption::PARAMETER_NONE, 'Whether to force installation of required dependencies', null),
    ));

    $this->aliases = array('plugin-install');
    $this->namespace = 'plugin';
    $this->name = 'install';

    $this->briefDescription = 'Installs a plugin';

    $this->detailedDescription = <<<EOF
The [plugin:install|INFO] task installs a plugin:

  [./symfony plugin:install sfGuargPlugin|INFO]

By default, it installs the latest [stable|COMMENT] release.

If you want to install a plugin that is not stable yet,
use the [stability|COMMENT] option:

  [./symfony plugin:install --stability=beta sfGuargPlugin|INFO]
  [./symfony plugin:install -s beta sfGuargPlugin|INFO]

You can also force the installation of a specific version:

  [./symfony plugin:install --release=1.0.0 sfGuargPlugin|INFO]
  [./symfony plugin:install -r 1.0.0 sfGuargPlugin|INFO]

To force installation of all required dependencies, use the [install_deps|INFO] flag:

  [./symfony plugin:install --install-deps sfGuargPlugin|INFO]
  [./symfony plugin:install -d sfGuargPlugin|INFO]

By default, the PEAR channel used is [symfony-plugins|INFO]
(plugins.symfony-project.org).

You can specify another channel with the [channel|COMMENT] option:

  [./symfony plugin:install --channel=mypearchannel sfGuargPlugin|INFO]
  [./symfony plugin:install -c mypearchannel sfGuargPlugin|INFO]

Or you can use the [channel/package|INFO] notation:

  [./symfony plugin:install mypearchannel/sfGuargPlugin|INFO]

You can also install PEAR packages hosted on a website:

  [./symfony plugin:install http://somewhere.example.com/sfGuargPlugin-1.0.0.tgz|INFO]

Or local PEAR packages:

  [./symfony plugin:install /home/fabien/plugins/sfGuargPlugin-1.0.0.tgz|INFO]

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
    $this->logSection('plugin', sprintf('installing plugin "%s"', $arguments['name']));

    $options['version'] = $options['release'];
    unset($options['release']);

    $this->getPluginManager()->installPlugin($arguments['name'], $options);
  }
}
