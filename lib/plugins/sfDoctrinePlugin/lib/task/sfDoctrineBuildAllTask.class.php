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
 * Generates Doctrine model, SQL and initializes the database.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrineBuildAllTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('doctrine-build-all');
    $this->namespace = 'doctrine';
    $this->name = 'build-all';
    $this->briefDescription = 'Generates Doctrine model, SQL and initializes the database';

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('skip-forms', 'F', sfCommandOption::PARAMETER_NONE, 'Skip generating forms'),
      new sfCommandOption('migrate', null, sfCommandOption::PARAMETER_NONE, 'Migrate instead of reset the database'),
    ));

    $this->detailedDescription = <<<EOF
The [doctrine:build-all|INFO] task is a shortcut for three other tasks:

  [./symfony doctrine:build-all|INFO]

The task is equivalent to:

  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:build-sql|INFO]
  [./symfony doctrine:build-forms|INFO]
  [./symfony doctrine:insert-sql|INFO]

See those three tasks help page for more information.

To bypass the confirmation, you can pass the [no-confirmation|COMMENT]
option:

  [./symfony doctrine:buil-all-load --no-confirmation|INFO]

Include the [--migrate|COMMENT] option if you would like to run your project's
migrations rather than inserting the Doctrine SQL.

  [./symfony doctrine:build-all --migrate|INFO]

This is equivalent to:

  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:build-sql|INFO]
  [./symfony doctrine:build-forms|INFO]
  [./symfony doctrine:migrate|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $buildDb = new sfDoctrineBuildDbTask($this->dispatcher, $this->formatter);
    $buildDb->setCommandApplication($this->commandApplication);
    $buildDb->setConfiguration($this->configuration);
    $ret = $buildDb->run();

    if ($ret)
    {
      return $ret;
    }

    $buildModel = new sfDoctrineBuildModelTask($this->dispatcher, $this->formatter);
    $buildModel->setCommandApplication($this->commandApplication);
    $buildModel->setConfiguration($this->configuration);
    $ret = $buildModel->run();

    if ($ret)
    {
      return $ret;
    }

    $buildSql = new sfDoctrineBuildSqlTask($this->dispatcher, $this->formatter);
    $buildSql->setCommandApplication($this->commandApplication);
    $buildSql->setConfiguration($this->configuration);
    $ret = $buildSql->run();

    if ($ret)
    {
      return $ret;
    }

    if (!$options['skip-forms'])
    {
      $buildForms = new sfDoctrineBuildFormsTask($this->dispatcher, $this->formatter);
      $buildForms->setCommandApplication($this->commandApplication);
      $buildForms->setConfiguration($this->configuration);
      $ret = $buildForms->run();

      if ($ret)
      {
        return $ret;
      }

      $buildFilters = new sfDoctrineBuildFiltersTask($this->dispatcher, $this->formatter);
      $buildFilters->setCommandApplication($this->commandApplication);
      $buildFilters->setConfiguration($this->configuration);
      $ret = $buildFilters->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if ($options['migrate'])
    {
      $migrate = new sfDoctrineMigrateTask($this->dispatcher, $this->formatter);
      $migrate->setCommandApplication($this->commandApplication);
      $migrate->setConfiguration($this->configuration);
      $ret = $migrate->run();
    }
    else
    {
      $insertSql = new sfDoctrineInsertSqlTask($this->dispatcher, $this->formatter);
      $insertSql->setCommandApplication($this->commandApplication);
      $insertSql->setConfiguration($this->configuration);
      $ret = $insertSql->run();
    }

    return $ret;
  }
}