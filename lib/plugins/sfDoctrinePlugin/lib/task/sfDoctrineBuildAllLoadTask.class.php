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
 * Creates Databases, Generates Doctrine model, SQL, initializes database, and load data.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrineBuildAllLoadTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('skip-forms', 'F', sfCommandOption::PARAMETER_NONE, 'Skip generating forms'),
      new sfCommandOption('migrate', null, sfCommandOption::PARAMETER_NONE, 'Migrate instead of reset the database'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The directories to look for fixtures'),
    ));

    $this->aliases = array('doctrine-build-all-load');
    $this->namespace = 'doctrine';
    $this->name = 'build-all-load';
    $this->briefDescription = 'Generates Doctrine model, SQL, initializes database, and loads fixtures data';

    $this->detailedDescription = <<<EOF
The [doctrine:build-all-load|INFO] task is a shortcut for two other tasks:

  [./symfony doctrine:build-all-load|INFO]

The task is equivalent to:

  [./symfony doctrine:build-all|INFO]
  [./symfony doctrine:data-load|INFO]

The task takes an application argument because of the [doctrine:data-load|COMMENT]
task. See [doctrine:data-load|COMMENT] help page for more information.

To bypass the confirmation, you can pass the [no-confirmation|COMMENT]
option:

  [./symfony doctrine:build-all-load --no-confirmation|INFO]

Include the [--migrate|COMMENT] option if you would like to run your project's
migrations rather than inserting the Doctrine SQL.

  [./symfony doctrine:build-all-load --migrate|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $buildAll = new sfDoctrineBuildAllTask($this->dispatcher, $this->formatter);
    $buildAll->setCommandApplication($this->commandApplication);
    $buildAll->setConfiguration($this->configuration);
    $ret = $buildAll->run(array(), array(
      'skip-forms'      => $options['skip-forms'],
      'migrate'         => $options['migrate'],
      'no-confirmation' => $options['no-confirmation'],
    ));

    if (0 == $ret)
    {
      $loadData = new sfDoctrineDataLoadTask($this->dispatcher, $this->formatter);
      $loadData->setCommandApplication($this->commandApplication);
      $loadData->setConfiguration($this->configuration);
      $loadData->run(array(), array(
        'dir' => $options['dir'],
      ));
    }

    return $ret;
  }
}