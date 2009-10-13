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
 * Delete all generated model classes for models which no longer exist in your YAML schema
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineCreateModelTables.class.php 16087 2009-03-07 22:08:50Z Jonathan.Wage $
 */
class sfDoctrineCleanModelFilesTask extends sfDoctrineBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array('doctrine:clean');
    $this->namespace = 'doctrine';
    $this->name = 'clean-model-files';
    $this->briefDescription = 'Delete all generated model classes for models which no longer exist in your YAML schema';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if ($modelsToRemove = array_diff($this->getFileModels(), $this->getYamlModels()))
    {
      $deleteModelFiles = new sfDoctrineDeleteModelFilesTask($this->dispatcher, $this->formatter);
      $deleteModelFiles->setCommandApplication($this->commandApplication);
      $deleteModelFiles->setConfiguration($this->configuration);
      $deleteModelFiles->run($modelsToRemove, array('no-confirmation' => $options['no-confirmation']));

      $this->reloadAutoload();
    }
    else
    {
      $this->logSection('doctrine', 'Could not find any models that need to be removed');
    }
  }

  /**
   * Returns models defined in YAML.
   * 
   * @return array
   */
  protected function getYamlModels()
  {
    $finder = sfFinder::type('file')->name('*.yml');
    $paths = array_merge(array(sfConfig::get('sf_config_dir').'/doctrine'), $this->configuration->getPluginSubpaths('/config/doctrine'));

    $models = array();
    foreach ($finder->in($paths) as $file)
    {
      $models = array_merge($models, array_keys((array) sfYaml::load($file)));
    }

    return array_unique($models);
  }

  /**
   * Returns models that have class files.
   * 
   * @return array
   */
  protected function getFileModels()
  {
    Doctrine_Core::loadModels(sfConfig::get('sf_lib_dir').'/model/doctrine');
    return Doctrine_Core::getLoadedModels();
  }
}
