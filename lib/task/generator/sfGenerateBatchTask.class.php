<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new batch.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerateBatchTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('script', sfCommandArgument::REQUIRED, 'The script name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 'dev'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Sets debug mode to true'),
    ));

    $this->aliases = array('init-batch');
    $this->namespace = 'generate';
    $this->name = 'batch';

    $this->briefDescription = 'Generates a new batch';

    $this->detailedDescription = <<<EOF
The [generate:batch|INFO] task creates a new batch script:

  [./symfony generate:batch frontend import|INFO]

It creates a file named [batch/%name%.php|COMMENT].

You can also enable the debug mode and change the environment by
using the [debug|COMMENT] and [env|COMMENT] options:

  [./symfony generate:batch --debug --env="staging" frontend import|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/batch/default.php|COMMENT] file.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app   = $arguments['application'];
    $batch = $arguments['script'];

    if (is_readable(sfConfig::get('sf_data_dir').'/skeleton/batch/default.php'))
    {
      $skeleton = sfConfig::get('sf_data_dir').'/skeleton/batch/default.php';
    }
    else
    {
      $skeleton = sfConfig::get('sf_symfony_data_dir').'/skeleton/batch/default.php';
    }

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

    $constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'APP_NAME'     => $app,
      'BATCH_NAME'   => $batch,
      'ENV_NAME'     => $options['env'],
      'DEBUG'        => $options['debug'] ? 'true' : 'false',
    );

    $this->filesystem->copy($skeleton, sfConfig::get('sf_bin_dir').'/'.$batch.'.php');
    $this->filesystem->replaceTokens(sfConfig::get('sf_bin_dir').DIRECTORY_SEPARATOR.$batch.'.php', '##', '##', $constants);
  }
}
