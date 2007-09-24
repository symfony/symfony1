<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Lists installed plugins.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPluginListTask extends sfPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('plugin-list');
    $this->namespace = 'plugin';
    $this->name = 'list';

    $this->briefDescription = 'Lists installed plugins';

    $this->detailedDescription = <<<EOF
The [plugin:list|INFO] task lists all installed plugins:

  [./symfony plugin:list|INFO]

It also gives the channel and version for each plugin.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format('Installed plugins:', 'COMMENT'))));

    foreach ($this->getPuginManager()->getInstalledPlugins() as $package)
    {
      $alias = $this->getPuginManager()->getRegistry()->getChannel($package->getChannel())->getAlias();
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array(sprintf(' %-40s %10s-%-6s %s', $this->formatter->format($package->getPackage(), 'INFO'), $package->getVersion(), $package->getState() ? $package->getState() : null, $this->formatter->format(sprintf('# %s (%s)', $package->getChannel(), $alias), 'COMMENT')))));
    }
  }
}
