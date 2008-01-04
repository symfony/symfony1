<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new controller.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerateControllerTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('env', sfCommandArgument::REQUIRED, 'The environment name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('filename', null, sfCommandOption::PARAMETER_REQUIRED, 'The script name'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Sets debug mode to true'),
    ));

    $this->aliases = array('init-controller');
    $this->namespace = 'generate';
    $this->name = 'controller';

    $this->briefDescription = 'Generates a new controller';

    $this->detailedDescription = <<<EOF
The [generate:controller|INFO] task creates a new front controller script:

  [./symfony generate:controller frontend staging|INFO]

By default, it creates a file named [web/%application%_%env%.php|COMMENT].
You can customize the script name by passing a [--filename|COMMENT] option:

  [./symfony generate:controller --filename="staging" frontend staging|INFO]

If you want to create a controller with debugging enabled,
pass the [--debug|COMMENT] option:

  [./symfony generate:controller --debug frontend staging|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $arguments['env'];

    $controller = isset($options['filename']) ? $options['filename'] : $app.'_'.$env;

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

    $constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'APP_NAME'        => $app,
      'CONTROLLER_NAME' => $controller,
      'ENV_NAME'        => $env,
      'DEBUG'           => (boolean) $options['debug'],
    );

    $this->filesystem->copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/controller/controller.php', sfConfig::get('sf_web_dir').'/'.$controller.'.php');
    $this->filesystem->replaceTokens(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$controller.'.php', '##', '##', $constants);
  }
}
