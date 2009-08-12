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
 * Generates code based on your schema.
 *
 * @package    sfDoctrinePlugin
 * @subpackage task
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfDoctrineBuildTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Whether to force dropping of the database'),
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_NONE, 'Runs all code-generating options'),
      new sfCommandOption('db', null, sfCommandOption::PARAMETER_NONE, 'Drop, create, and either insert SQL or migrate the database'),
      new sfCommandOption('model', null, sfCommandOption::PARAMETER_NONE, 'Build model classes'),
      new sfCommandOption('forms', null, sfCommandOption::PARAMETER_NONE, 'Build form classes'),
      new sfCommandOption('filters', null, sfCommandOption::PARAMETER_NONE, 'Build filter classes'),
      new sfCommandOption('sql', null, sfCommandOption::PARAMETER_NONE, 'Build SQL'),
      new sfCommandOption('and-migrate', null, sfCommandOption::PARAMETER_NONE, 'Migrate the database'),
      new sfCommandOption('and-load', null, sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Load fixture data'),
      new sfCommandOption('and-append', null, sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Append fixture data'),
    ));

    $this->namespace = 'doctrine';
    $this->name = 'build';

    $this->briefDescription = 'Generate code based on your schema';

    $this->detailedDescription = <<<EOF
The [doctrine:build|INFO] task generates code based on your schema:

  [./symfony doctrine:build|INFO]

You must specify what you would like built. If you want model and form classes
built use the [--model|COMMENT] and [--forms|COMMENT] options:

  [./symfony doctrine:build --model --forms|INFO]

You can also use the [--all|COMMENT] option if you would like all classes and SQL files
to be built.

  [./symfony doctrine:build --all|INFO]

This is equivalent to running the following tasks:

  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:build-forms|INFO]
  [./symfony doctrine:build-filters|INFO]
  [./symfony doctrine:build-sql|INFO]

The [--and-migrate|COMMENT] option will run any pending migrations once the building
tasks are complete:

  [./symfony doctrine:build --db --and-migrate|INFO]

The [--and-load|COMMENT] option will load data from the project and plugin
[/data/fixtures|COMMENT] directories:

  [./symfony doctrine:build --all --and-load|INFO]

To specify what fixtures are loaded, add a parameter to the [--and-load|COMMENT] option:

  [./symfony doctrine:build --all --and-load=data/fixtures/dev/|INFO]

To append fixture data without erasing any records from the database, include
the [--and-append|COMMENT] option:

  [./symfony doctrine:build --all --and-append|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if ($options['db'])
    {
      $task = new sfDoctrineDropDbTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run(array(), array('no-confirmation' => $options['no-confirmation']));

      if ($ret)
      {
        return $ret;
      }

      $task = new sfDoctrineBuildDbTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }

      // :insert-sql (or :migrate) will also be run, below
    }

    if ($options['model'] || $options['forms'] || $options['filters'] || $options['sql'] || $options['all'])
    {
      $task = new sfDoctrineBuildModelTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if ($options['forms'] || $options['all'])
    {
      $task = new sfDoctrineBuildFormsTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if ($options['filters'] || $options['all'])
    {
      $task = new sfDoctrineBuildFiltersTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if ($options['sql'] || $options['all'])
    {
      $task = new sfDoctrineBuildSqlTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if ($options['and-migrate'])
    {
      $task = new sfDoctrineMigrateTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }
    else if ($options['db'])
    {
      $task = new sfDoctrineInsertSqlTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);
      $ret = $task->run();

      if ($ret)
      {
        return $ret;
      }
    }

    if (count($options['and-load']) || count($options['and-append']))
    {
      $task = new sfDoctrineDataLoadTask($this->dispatcher, $this->formatter);
      $task->setCommandApplication($this->commandApplication);
      $task->setConfiguration($this->configuration);

      if (count($options['and-load']))
      {
        $ret = $task->run(array(), array(
          'dir' => in_array(array(), $options['and-load'], true) ? null : $options['and-load'],
        ));

        if ($ret)
        {
          return $ret;
        }
      }

      if (count($options['and-append']))
      {
        $ret = $task->run(array(), array(
          'dir'    => in_array(array(), $options['and-append'], true) ? null : $options['and-append'],
          'append' => true,
        ));

        if ($ret)
        {
          return $ret;
        }
      }
    }
  }
}
