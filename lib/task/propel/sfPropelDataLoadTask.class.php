<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loads data from fixtures directory.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelLoadDataTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('append', null, sfCommandOption::PARAMETER_NONE, 'Don\'t delete current data in the database'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
    ));

    $this->aliases = array('propel-load-data');
    $this->namespace = 'propel';
    $this->name = 'data-load';
    $this->briefDescription = 'Loads data from fixtures directory';

    $this->detailedDescription = <<<EOF
The [propel:data-load|INFO] task loads data fixtures into the database:

  [./symfony propel:data-load frontend|INFO]

The task loads data from all the files found in [data/fixtures/|COMMENT].

If you want to load data from other directories, you can use
the [--dir|COMMENT] option:

  [./symfony propel:data-load --dir="data/fixtures" --dir="data/data" frontend|INFO]

The task use the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [--connection|COMMENT] option:

  [./symfony propel:data-load --connection="name" frontend|INFO]

If you don't want the task to remove existing data in the database,
use the [--append|COMMENT] option:

  [./symfony propel:data-load --append frontend|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->bootstrapSymfony($arguments['application'], $options['env'], true);

    if (!is_null($this->commandApplication))
    {
      $this->commandApplication->getAutoloader()->unregister();
      $this->commandApplication->getAutoloader()->register();
    }

    if (count($options['dir']))
    {
      $fixturesDirs = $options['dir'];
    }
    else
    {
      if (!$pluginDirs = glob(sfConfig::get('sf_root_dir').'/plugins/*/data'))
      {
        $pluginDirs = array();
      }
      $fixturesDirs = sfFinder::type('dir')->name('fixtures')->in(array_merge($pluginDirs, array(sfConfig::get('sf_data_dir'))));
    }

    $databaseManager = new sfDatabaseManager();
    $databaseManager->initialize();

    $data = new sfPropelData();
    $data->setDeleteCurrentData(isset($options['append']) ? false : true);

    foreach ($fixturesDirs as $fixturesDir)
    {
      if (!is_readable($fixturesDir))
      {
        continue;
      }

      $this->log($this->formatSection('propel', sprintf('load data from "%s"', $fixturesDir)));
      $data->loadData($fixturesDir, $options['connection']);
    }
  }
}
