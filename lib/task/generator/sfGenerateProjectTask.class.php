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

    $this->addOptions(array(
      new sfCommandOption('orm', null, sfCommandOption::PARAMETER_REQUIRED, 'The ORM to use by default', 'Doctrine'),
      new sfCommandOption('installer', null, sfCommandOption::PARAMETER_REQUIRED, 'An installer script to execute', null),
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

By default, the task configures Doctrine as the ORM. If you want to use
Propel, use the [--orm|INFO] option:

  [./symfony generate:project blog --orm=Propel|INFO]

You can also pass the [--installer|INFO] option to further customize the
project:

  [./symfony generate:project blog --installer=./installer.php|INFO]
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
    $this->installDir(dirname(__FILE__).'/skeleton/project');

    // execute the choosen ORM installer script
    include dirname(__FILE__).'/../../plugins/sf'.ucfirst(strtolower($options['orm'])).'Plugin/config/installer.php';

    $this->arguments = $arguments;
    $this->options = $options;

    $this->replaceTokens();

    // execute a custom installer
    if ($options['installer'] && $this->commandApplication)
    {
      $this->reloadTasks();

      include $options['installer'];
    }

    // fix permission for common directories
    $fixPerms = new sfProjectPermissionsTask($this->dispatcher, $this->formatter);
    $fixPerms->setCommandApplication($this->commandApplication);
    $fixPerms->run();

    $this->replaceTokens();
  }

  /**
   * Executes another task in the context of the current one.
   *
   * @param  string  $name      The name of the task to execute
   * @param  array   $arguments An array of arguments to pass to the task
   * @param  array   $options   An array of options to pass to the task
   *
   * @return Boolean The returned value of the task run() method
   */
  protected function runTask($name, $arguments = array(), $options = array())
  {
    if (is_null($this->commandApplication))
    {
      throw new LogicException('No command application associated with this task yet.');
    }

    $task = $this->commandApplication->getTaskToExecute($name);
    $task->setCommandApplication($this->commandApplication);

    return $task->run($arguments, $options);
  }

  /**
   * Mirrors a directory structure inside the created project.
   *
   * @param string   $dir    The directory to mirror
   * @param sfFinder $finder A sfFinder instance to use for the mirroring
   */
  protected function installDir($dir, $finder = null)
  {
    if (is_null($finder))
    {
      $finder = sfFinder::type('any')->discard('.sf');
    }

    $this->getFilesystem()->mirror($dir, sfConfig::get('sf_root_dir'), $finder);
  }

  /**
   * Replaces tokens in files contained in a given directory.
   *
   * If you don't pass a directory, it will replace in the config/ and lib/ directory.
   *
   * @param array $dirs   An array of directory where to do the replacement
   * @param array $tokens An array of tokens to use
   */
  protected function replaceTokens($dirs = array(), $tokens = array())
  {
    if (!$dirs)
    {
      $dirs = array(sfConfig::get('sf_config_dir'), sfConfig::get('sf_lib_dir'));
    }

    // update ProjectConfiguration class (use a relative path when the symfony core is nested within the project)
    $symfonyCoreAutoload = 0 === strpos(sfConfig::get('sf_symfony_lib_dir'), sfConfig::get('sf_root_dir')) ?
      sprintf('dirname(__FILE__).\'/..%s/autoload/sfCoreAutoload.class.php\'', str_replace(sfConfig::get('sf_root_dir'), '', sfConfig::get('sf_symfony_lib_dir'))) :
      var_export(sfConfig::get('sf_symfony_lib_dir').'/autoload/sfCoreAutoload.class.php', true);

    $tokens = array_merge(array(
      'ORM'                   => $this->options['orm'],
      'OTHER_ORM'             => 'Doctrine' == $this->options['orm'] ? 'Propel' : 'Doctrine',
      'PROJECT_NAME'          => $this->arguments['name'],
      'PROJECT_DIR'           => sfConfig::get('sf_root_dir'),
      'SYMFONY_CORE_AUTOLOAD' => $symfonyCoreAutoload,
    ), $tokens);

    $this->getFilesystem()->replaceTokens(sfFinder::type('file')->prune('vendor')->in($dirs), '##', '##', $tokens);
  }

  /**
   * Reloads tasks.
   *
   * Useful when you install plugins with tasks and if you want to use them with the runTask() method.
   */
  protected function reloadTasks()
  {
    $this->configuration = $this->createConfiguration(null, null);

    $this->commandApplication->clearTasks();
    $this->commandApplication->loadTasks($this->configuration);

    $tasks = array();
    foreach (get_declared_classes() as $class)
    {
      $r = new Reflectionclass($class);
      if ($r->isSubclassOf('sfTask') && !$r->isAbstract() && false === strpos($class, 'Doctrine' == $this->options['orm'] ? 'Propel' : 'Doctrine'))
      {
        $tasks[] = new $class($this->dispatcher, $this->formatter);
      }
    }

    $this->commandApplication->registerTasks($tasks);
  }
}
