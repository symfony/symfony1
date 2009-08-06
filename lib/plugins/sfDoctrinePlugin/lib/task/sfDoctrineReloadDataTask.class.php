<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Drops database, recreates it, inserts the sql and loads the data fixtures
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRebuildDbTask.class.php 16087 2009-03-07 22:08:50Z Kris.Wallsmith $
 */
class sfDoctrineReloadDataTask extends sfDoctrineBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
      new sfCommandOption('migrate', null, sfCommandOption::PARAMETER_NONE, 'Migrate instead of reset the database'),
    ));

    $this->namespace        = 'doctrine';
    $this->name             = 'reload-data';
    $this->aliases = array('doctrine-reload-data');    
    $this->briefDescription = 'Reloads databases and fixtures for your project';
    $this->detailedDescription = <<<EOF
The [doctrine:reload-data|INFO] task drops the database, recreates it and loads fixtures
Call it with:

  [php symfony doctrine:reload-data|INFO]
  
The task is equivalent to:

  [./symfony doctrine:drop-db|INFO]
  [./symfony doctrine:build-db|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load|INFO]  
  
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $dropDb = new sfDoctrineDropDbTask($this->dispatcher, $this->formatter);
    $dropDb->setCommandApplication($this->commandApplication);
    $dropDb->setConfiguration($this->configuration);
    $dropDb->run(array(), array(
      'no-confirmation' => $options['no-confirmation'],
    ));

    $buildDb = new sfDoctrineBuildDbTask($this->dispatcher, $this->formatter);
    $buildDb->setCommandApplication($this->commandApplication);
    $buildDb->setConfiguration($this->configuration);
    $ret = $buildDb->run();    

    if ($ret)
    {
      return $ret;
    }

    Doctrine::initializeModels(Doctrine::loadModels(sfConfig::get('sf_lib_dir').'/model/doctrine'));

    if ($options['migrate'])
    {
      $migrateTask = new sfDoctrineMigrateTask($this->dispatcher, $this->formatter);
      $migrateTask->setCommandApplication($this->commandApplication);
      $migrateTask->setConfiguration($this->configuration);
      $ret = $migrateTask->run();
    }
    else
    {
      $insertSql = new sfDoctrineInsertSqlTask($this->dispatcher, $this->formatter);
      $insertSql->setCommandApplication($this->commandApplication);
      $insertSql->setConfiguration($this->configuration);
      $ret = $insertSql->run();
    }

    if ($ret)
    {
      return $ret;
    }    

    $loadData = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $loadData->setCommandApplication($this->commandApplication);
    $loadData->setConfiguration($this->configuration);
    $ret = $loadData->run(array(), array(
      'dir' => $options['dir'],
    ));
    
    return $ret;
  }
}