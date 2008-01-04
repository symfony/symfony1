<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new plugin.
 *
 * @package    symfony
 * @subpackage task
 * @author     Romain Dorgueil <romain.dorgueil@dakrazy.net>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGeneratePluginTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->process($commandManager, $options);

    $this->checkProjectExists();

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
  }

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('plugin', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->aliases = array();
    $this->namespace = 'generate';
    $this->name = 'plugin';

    $this->briefDescription = 'Generates a new plugin';

    $this->detailedDescription = <<<EOF
The [generate:plugin|INFO] task creates the basic directory structure
for a new plugin in the current project:

  [./symfony generate:plugin myDummyPlugin|INFO]

If a plugin with the same name already exists,
it throws a [sfCommandException|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin = $arguments['plugin'];

    $pluginDir = sfConfig::get('sf_root_dir').'/'.sfConfig::get('sf_plugins_dir_name').'/'.$plugin;

    if (is_dir($pluginDir))
    {
      throw new sfCommandException(sprintf('The plugin "%s" already exists.', $pluginDir));
    }

    // Create basic plugin structure
    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $this->filesystem->mirror(sfConfig::get('sf_symfony_data_dir').'/skeleton/plugin', $pluginDir, $finder);

    $fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
    $fixPerms->run();
  }
}
