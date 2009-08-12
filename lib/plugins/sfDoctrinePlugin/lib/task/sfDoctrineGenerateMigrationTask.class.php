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
 * Inserts SQL for current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrineGenerateMigrationTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the migration'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('editor-cmd', null, sfCommandOption::PARAMETER_REQUIRED, 'Open script with this command upon creation'),
    ));

    $this->aliases = array('doctrine-generate-migration');
    $this->namespace = 'doctrine';
    $this->name = 'generate-migration';
    $this->briefDescription = 'Generate migration class';

    $this->detailedDescription = <<<EOF
The [doctrine:generate-migration|INFO] task generates migration template

  [./symfony doctrine:generate-migration AddUserEmailColumn|INFO]

You can provide an [--editor-cmd|COMMENT] option to open the new migration class in your
editor of choice upon creation:

  [./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('doctrine', sprintf('generating migration class named "%s"', $arguments['name']));

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->callDoctrineCli('generate-migration', array('name' => $arguments['name']));

    if (isset($options['editor-cmd']))
    {
      $config = $this->getCliConfig();
      $files = sfFinder::type('file')->sort_by_name()->name('*.php')->in($config['migrations_path']);

      $this->getFilesystem()->sh($options['editor-cmd'].' '.escapeshellarg(array_pop($files)));
    }
  }
}
