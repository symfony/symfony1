<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfPropelBaseTask.class.php');

/**
 * Generates a Propel CRUD module.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelGenerateCrudTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('module', sfCommandArgument::REQUIRED, 'The module name'),
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model class name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('theme', null, sfCommandOption::PARAMETER_REQUIRED, 'The theme name', 'default'),
      new sfCommandOption('generate-in-cache', null, sfCommandOption::PARAMETER_NONE, 'Generate the module in cache'),
      new sfCommandOption('non-atomic-actions', null, sfCommandOption::PARAMETER_NONE, 'Generate non atomic actions'),
      new sfCommandOption('non-verbose-templates', null, sfCommandOption::PARAMETER_NONE, 'Generate non verbose templates'),
      new sfCommandOption('with-show', null, sfCommandOption::PARAMETER_NONE, 'Generate a show method'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array('propel-generate-crud');
    $this->namespace = 'propel';
    $this->name = 'generate-crud';
    $this->briefDescription = 'Generates a Propel CRUD module';

    $this->detailedDescription = <<<EOF
The [propel:generate-crud|INFO] task generates a Propel CRUD module:

  [./symfony propel:generate-crud frontend article Article|INFO]

The task creates a [%module%|COMMENT] module in the [%application%|COMMENT] application
for the model class [%model%|COMMENT].

You can also create an empty module that inherits its actions and templates from
a runtime generated module in [%sf_app_cache_dir%/modules/auto%module%|COMMENT] by
using the [--generate-in-cache|COMMENT] option:

  [./symfony propel:generate-crud --generate-in-cache frontend article Article|INFO]

The generator can use a customized theme by using the [--theme|COMMENT] option:

  [./symfony propel:generate-crud --theme="custom" frontend article Article|INFO]

This way, you can create your very own CRUD generator with your own conventions.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

    $this->constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'APP_NAME'     => $arguments['application'],
      'MODULE_NAME'  => $arguments['module'],
      'MODEL_CLASS'  => $arguments['model'],
      'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
    );

    $method = $options['generate-in-cache'] ? 'executeInit' : 'executeGenerate';

    $this->$method($arguments, $options);
  }

  protected function executeGenerate($arguments = array(), $options = array())
  {
    $this->bootstrapSymfony($arguments['application'], $options['env'], true);

    sfSimpleAutoload::getInstance()->unregister();
    sfSimpleAutoload::getInstance()->register();

    $databaseManager = new sfDatabaseManager();

    // generate module
    $tmpDir = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
    sfConfig::set('sf_module_cache_dir', $tmpDir);
    $generatorManager = new sfGeneratorManager();
    $generatorManager->generate('sfPropelCrudGenerator', array(
      'model_class'           => $arguments['model'],
      'moduleName'            => $arguments['module'],
      'theme'                 => $options['theme'],
      'non_atomic_actions'    => $options['non-atomic-actions'],
      'non_verbose_templates' => $options['non-verbose-templates'],
      'with_show'             => $options['with-show'],
    ));

    $moduleDir = sfConfig::get('sf_root_dir').'/'.sfConfig::get('sf_apps_dir_name').'/'.$arguments['application'].'/'.sfConfig::get('sf_app_module_dir_name').'/'.$arguments['module'];

    // copy our generated module
    $this->filesystem->mirror($tmpDir.'/auto'.ucfirst($arguments['module']), $moduleDir, sfFinder::type('any'));

    if (!$options['with-show'])
    {
      $this->filesystem->remove($moduleDir.'/templates/showSuccess.php');
    }

    // change module name
    $this->filesystem->replaceTokens($moduleDir.'/actions/actions.class.php', '', '', array('auto'.ucfirst($arguments['module']) => $arguments['module']));

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->filesystem->replaceTokens($finder->in($moduleDir), '##', '##', $this->constants);

    // create basic test
    $this->filesystem->copy(sfConfig::get('sf_symfony_lib_dir').'/task/generator/skeleton/module/test/actionsTest.php', sfConfig::get('sf_test_dir').'/functional/'.$arguments['application'].'/'.$arguments['module'].'ActionsTest.php');

    // customize test file
    $this->filesystem->replaceTokens(sfConfig::get('sf_test_dir').'/functional/'.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php', '##', '##', $this->constants);

    // delete temp files
    $this->filesystem->remove(sfFinder::type('any')->in($tmpDir));
  }

  protected function executeInit($arguments = array(), $options = array())
  {
    $moduleDir = sfConfig::get('sf_root_dir').'/'.sfConfig::get('sf_apps_dir_name').'/'.$arguments['application'].'/'.sfConfig::get('sf_app_module_dir_name').'/'.$arguments['module'];

    // create basic application structure
    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $dirs = sfLoader::getGeneratorSkeletonDirs('sfPropelCrud', $options['theme']);
    foreach ($dirs as $dir)
    {
      if (is_dir($dir))
      {
        $this->filesystem->mirror($dir, $moduleDir, $finder);
        break;
      }
    }

    // create basic test
    $this->filesystem->copy(sfConfig::get('sf_symfony_lib_dir').'/task/generator/skeleton/module/test/actionsTest.php', sfConfig::get('sf_test_dir').'/functional/'.$arguments['application'].'/'.$arguments['module'].'ActionsTest.php');

    // customize test file
    $this->filesystem->replaceTokens(sfConfig::get('sf_test_dir').'/functional/'.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php', '##', '##', $this->constants);

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->constants['CONFIG'] = sprintf("    non_atomic_actions:    %s\n    non_verbose_templates: %s\n    with_show:             %s",
      $options['non-atomic-actions'] ? 'true' : 'false',
      $options['non-verbose-templates'] ? 'true' : 'false',
      $options['with-show'] ? 'true' : 'false'
    );
    $this->filesystem->replaceTokens($finder->in($moduleDir), '##', '##', $this->constants);
  }
}
