<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfGeneratorBaseTask.class.php');

/**
 * Generates a new project.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerateProjectTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->process($commandManager, $options);

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
  }

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The project name'),
    ));

    $this->aliases = array('init-project');
    $this->namespace = 'generate';
    $this->name = 'project';

    $this->briefDescription = 'Generates a new project';

    $this->detailedDescription = <<<EOF
The [generate:project|INFO] task creates the basic directory structure
for a new project in the current directory:

  [./symfony generate:project blog|INFO]

If the current directory already contains a symfony project,
it throws a [sfCommandException|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (file_exists('symfony'))
    {
      throw new sfCommandException(sprintf('A project named "%s" already exists in this directory.', $arguments['name']));
    }

    // Create basic project structure
    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $this->filesystem->mirror(sfConfig::get('sf_symfony_data_dir').'/skeleton/project', sfConfig::get('sf_root_dir'), $finder);

    // Update project name and directory
    $finder = sfFinder::type('file')->name('properties.ini', 'apache.conf', 'propel.ini');
    $this->filesystem->replaceTokens($finder->in(sfConfig::get('sf_config_dir')), '##', '##', array('PROJECT_NAME' => $arguments['name'],
                                                                                                    'PROJECT_DIR' => sfConfig::get('sf_root_dir')));

    // Update config/config.php
    $this->filesystem->replaceTokens(sfConfig::get('sf_config_dir').'/config.php', '##', '##', array(
      'SYMFONY_LIB_DIR'  => sfConfig::get('sf_symfony_lib_dir'),
      'SYMFONY_DATA_DIR' => sfConfig::get('sf_symfony_data_dir'),
    ));

    $fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
    $fixPerms->run();
  }
}
