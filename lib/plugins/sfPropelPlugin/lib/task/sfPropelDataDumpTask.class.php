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
 * Dumps data to the fixtures directory.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelDumpDataTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('target', sfCommandArgument::OPTIONAL, 'The target filename'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->aliases = array('propel-dump-data');
    $this->namespace = 'propel';
    $this->name = 'data-dump';
    $this->briefDescription = 'Dumps data to the fixtures directory';

    $this->detailedDescription = <<<EOF
The [propel:data-dump|INFO] task dumps database data:

  [./symfony propel:data-dump frontend > data/fixtures/dump.yml|INFO]

By default, the task outputs the data to the standard output,
but you can also pass a filename as a second argument:

  [./symfony propel:data-dump frontend dump.yml|INFO]

The task will dump data in [data/fixtures/%target%|COMMENT]
(data/fixtures/dump.yml in the example).

The dump file is in the YML format and can be re-imported by using
the [propel:data-load|INFO] task.

By default, the task use the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [connection|COMMENT] option:

  [./symfony propel:data-load --connection="name" frontend|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true);

    $databaseManager = new sfDatabaseManager($configuration);

    $filename = $arguments['target'];
    if (!is_null($filename) && !sfToolkit::isPathAbsolute($filename))
    {
      $dir = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'fixtures';
      $this->getFilesystem()->mkdirs($dir);
      $filename = $dir.DIRECTORY_SEPARATOR.$filename;

      $this->logSection('propel', sprintf('dumping data to "%s"', $filename));
    }

    $data = new sfPropelData();

    if (!is_null($filename))
    {
      $data->dumpData($filename, 'all', $options['connection']);
    }
    else
    {
      fwrite(STDOUT, sfYaml::dump($data->getData('all', $options['connection']), 3));
    }
  }
}
