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
    $this->log($this->format("Installed plugins:\n", 'COMMENT'));

    $config = $this->pearInit();
    $registry = $config->getRegistry();
    $installed = $registry->packageInfo(null, null, null);
    foreach ($installed as $channel => $packages)
    {
      foreach ($packages as $package)
      {
        $pobj = $registry->getPackage(isset($package['package']) ? $package['package'] : $package['name'], $channel);
        $this->log(sprintf(" %-40s %10s-%-6s %s\n", $this->format($pobj->getPackage(), 'INFO'), $pobj->getVersion(), $pobj->getState() ? $pobj->getState() : null, $this->format(sprintf('# %s (%s)', $channel, $registry->getChannel($channel)->getAlias()), 'COMMENT')));
      }
    }
  }
}
