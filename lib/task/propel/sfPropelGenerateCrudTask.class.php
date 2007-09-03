<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    // generate module
    $tmpDir = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
    sfConfig::set('sf_module_cache_dir', $tmpDir);
    $generatorManager = new sfGeneratorManager();
    $generatorManager->generate('sfPropelCrudGenerator', array('model_class' => $arguments['model'], 'moduleName' => $arguments['module'], 'theme' => $options['theme']));

    $moduleDir = sfConfig::get('sf_root_dir').'/'.sfConfig::get('sf_apps_dir_name').'/'.$arguments['application'].'/'.sfConfig::get('sf_app_module_dir_name').'/'.$arguments['module'];

    // copy our generated module
    $this->filesystem->mirror($tmpDir.'/auto'.ucfirst($arguments['module']), $moduleDir, sfFinder::type('any'));

    // change module name
    $this->filesystem->replaceTokens($moduleDir.'/actions/actions.class.php', '', '', array('auto'.ucfirst($arguments['module']) => $arguments['module']));

    $constants = array(
      'PROJECT_NAME' => isset($properties['name']) ? $properties['name'] : 'symfony',
      'APP_NAME'     => $arguments['application'],
      'MODULE_NAME'  => $arguments['module'],
      'MODEL_CLASS'  => $arguments['model'],
      'AUTHOR_NAME'  => isset($properties['author']) ? $properties['author'] : 'Your name here',
    );

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->filesystem->replaceTokens($finder->in($moduleDir), '##', '##', $constants);

    // create basic test
    $this->filesystem->copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/module/test/actionsTest.php', sfConfig::get('sf_root_dir').'/test/functional/'.$arguments['application'].'/'.$arguments['module'].'ActionsTest.php');

    // customize test file
    $this->filesystem->replaceTokens(sfConfig::get('sf_root_dir').'/test/functional/'.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php', '##', '##', $constants);

    // delete temp files
    $this->filesystem->remove(sfFinder::type('any')->in($tmpDir));
  }
}
