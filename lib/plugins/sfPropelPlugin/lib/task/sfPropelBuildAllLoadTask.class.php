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
 * Generates Propel model, SQL, initializes database, and load data.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildAllLoadTask extends sfPropelBaseTask
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
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->aliases = array('propel-build-all-load');
    $this->namespace = 'propel';
    $this->name = 'build-all-load';
    $this->briefDescription = 'Generates Propel model, SQL, initializes database, and load data';

    $this->detailedDescription = <<<EOF
The [propel:build-all-load|INFO] task is a shortcut for four other tasks:

  [./symfony propel:build-all-load frontend|INFO]

The task is equivalent to:

  [./symfony propel:build-all|INFO]
  [./symfony propel:data-load frontend|INFO]

The task takes an application argument because of the [propel:data-load|COMMENT]
task. See [propel:data-load|COMMENT] help page for more information.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // load Propel configuration before Phing
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true);
    $databaseManager = new sfDatabaseManager($configuration);
    require_once sfConfig::get('sf_symfony_lib_dir').'/plugins/sfPropelPlugin/lib/propel/sfPropelAutoload.php';

    $buildAll = new sfPropelBuildAllTask($this->dispatcher, $this->formatter);
    $buildAll->setCommandApplication($this->commandApplication);
    $buildAll->run();

    $loadData = new sfPropelLoadDataTask($this->dispatcher, $this->formatter);
    $loadData->setCommandApplication($this->commandApplication);

    $loadData->run(array('application' => $arguments['application']), array('--env='.$options['env'], '--connection='.$options['connection']));
  }
}
