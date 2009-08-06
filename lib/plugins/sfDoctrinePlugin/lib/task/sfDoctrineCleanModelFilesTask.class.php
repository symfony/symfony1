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
    $paths = array();
    $paths[] = sfConfig::get('sf_config_dir').'/doctrine';

    $plugins = $this->configuration->getPlugins();
    foreach ($this->configuration->getAllPluginPaths() as $plugin => $path)
    {
      if (!in_array($plugin, $plugins))
      {
        continue;
      }
      $paths[] = $path.'/config/doctrine';
    }

    $files = sfFinder::type('file')
      ->name('*.yml')
      ->in($paths);

    $array = array();
    foreach ($files as $file)
    {
      $array = array_merge_recursive($array, (array) sfYaml::load($file));
    }
    $yamlModels = array_keys($array);

    $fileModels = Doctrine::loadModels(sfConfig::get('sf_lib_dir').'/model/doctrine');
    $fileModels = array_values($fileModels);
    asort($yamlModels);
    asort($fileModels);
    $modelsToRemove = array_diff($fileModels, $yamlModels);
    $modelsToRemove = array_values($modelsToRemove);

    if (!empty($modelsToRemove))
    {
      $this->logSection('doctrine', 'Found '.count($modelsToRemove).' models to remove.');
      foreach ($modelsToRemove as $model)
      {
        $this->logSection('doctrine', $model);
      }

      $deleteModelFiles = new sfDoctrineDeleteModelFilesTask($this->dispatcher, $this->formatter);
      $deleteModelFiles->setCommandApplication($this->commandApplication);
      $deleteModelFiles->setConfiguration($this->configuration);
      foreach ($modelsToRemove as $model)
      {
        $ret = $deleteModelFiles->run(array($model));
      }

      $cc = new sfCacheClearTask($this->dispatcher, $this->formatter);
      $cc->setCommandApplication($this->commandApplication);
      $cc->setConfiguration($this->configuration);
      $cc->run();
    }
    else
    {
      throw new sfException('Could not find any models that need to be removed!');
    }
  }
}