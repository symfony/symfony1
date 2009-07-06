<?php

/*
 * This file is part of the symfony package.
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Delete all generated files associated with a Doctrine model. Forms, filters, etc.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineCreateModelTables.class.php 16087 2009-03-07 22:08:50Z Jonathan.Wage $
 */
class sfDoctrineDeleteModelFilesTask extends sfDoctrineBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the model you wish to delete all related files for.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'delete-model-files';
    $this->briefDescription = 'Delete all the related auto generated files for a given model name.';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $modelName = $arguments['name'];

    if (!$options['no-confirmation'] && !$this->askConfirmation(array('This command will delete generated files related to the model named "'.$modelName.'"', 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('doctrine', 'Delete model task aborted');

      return 1;
    }

    $names = array(
      $modelName.'.class.php',
      $modelName.'Table.class.php',
      'Plugin'.$modelName.'.class.php',
      'Plugin'.$modelName.'Table.class.php',
      'Base'.$modelName.'.class.php',
      $modelName.'Form.class.php',
      'Plugin'.$modelName.'Form.class.php',
      'Base'.$modelName.'Form.class.php',
      $modelName.'FormFilter.class.php',
      'Plugin'.$modelName.'FormFilter.class.php',
      'Base'.$modelName.'FormFilter.class.php'
    );

    $pluginPaths = $this->configuration->getPluginPaths();
    $pluginLibDirs = sfFinder::type('dir')
      ->name('lib')
      ->maxdepth(1)
      ->in($pluginPaths);

    $in = array(
      sfConfig::get('sf_lib_dir'),
    );
    $in = array_merge($in, $pluginLibDirs);

    $files = sfFinder::type('file')
      ->name($names)
      ->in($in);

    if (empty($files))
    {
      throw new sfException('No files found for the model named "'.$modelName.'"');
    }

    $this->logSection('doctrine', 'Found '.count($files).' files related to the model named "'.$modelName.'"');
    $this->log(null);
    foreach ($files as $file)
    {
      $this->log('  '.$file);
    }
    $this->log(null);
    if (!$options['no-confirmation'] && !$this->askConfirmation(array('You are about to delete the above listed files!', 'Are you sure you want to proceed? (y/N)'), null, false))
    {
      $this->logSection('doctrine', 'Delete model task aborted');

      return 1;
    }

    foreach ($files as $file)
    {
      $this->getFilesystem()->remove($file);
    }
  }
}