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

    // create basic project structure
    $finder = sfFinder::type('any')->discard('.sf');
    $this->getFilesystem()->mirror(dirname(__FILE__).'/skeleton/project', sfConfig::get('sf_root_dir'), $finder);

    // update project name and directory
    $finder = sfFinder::type('file')->name('properties.ini', 'apache.conf', 'propel.ini', 'databases.yml');
    $this->getFileSystem()->replaceTokens($finder->in(sfConfig::get('sf_config_dir')), '##', '##', array('PROJECT_NAME' => $arguments['name'], 'PROJECT_DIR' => sfConfig::get('sf_root_dir')));

    // update ProjectConfiguration class (use a relative path when the symfony core is nested within the project)
    $symfonyCoreAutoload = 0 === strpos(sfConfig::get('sf_symfony_lib_dir'), sfConfig::get('sf_root_dir')) ?
      sprintf('dirname(__FILE__).\'/..%s/autoload/sfCoreAutoload.class.php\'', str_replace(sfConfig::get('sf_root_dir'), '', sfConfig::get('sf_symfony_lib_dir'))) :
      var_export(sfConfig::get('sf_symfony_lib_dir').'/autoload/sfCoreAutoload.class.php', true);
    $this->getFileSystem()->replaceTokens(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php', '##', '##', array('SYMFONY_CORE_AUTOLOAD' => $symfonyCoreAutoload));

    // update vhost sample file
    $this->getFileSystem()->replaceTokens(sfConfig::get('sf_config_dir').'/vhost.sample', '##', '##', array('PROJECT_NAME' => $arguments['name'], 'SYMFONY_WEB_DIR' => sfConfig::get('sf_web_dir'), 'SYMFONY_SF_DIR' => realpath(sfCoreAutoload::getInstance()->getBaseDir().'../data/web/sf')));

    // fix permission for common directories
    $fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
    $fixPerms->setCommandApplication($this->commandApplication);
    $fixPerms->run();

    // publish assets for core plugins
    $publishAssets = new sfPluginPublishAssetsTask($this->dispatcher, $this->formatter);
    $publishAssets->setCommandApplication($this->commandApplication);
    $publishAssets->run(array(), array('--core-only'));
  }
}
