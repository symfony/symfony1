<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Rotates an application log files.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogRotateTask extends sfBaseTask
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

    $this->aliases = array('log-rotate');
    $this->namespace = 'log';
    $this->name = 'rotate';
    $this->briefDescription = 'Rotates an application log files';

    $this->detailedDescription = <<<EOF
The [log:rotate|INFO] task rotates application log files for a given
environment:

  [./symfony log:rotate frontend |INFO]

This tasks uses the [logging.yml|COMMENT] file to configure the [period|COMMENT]
and [history|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $arguments['env'];

    $this->bootstrapSymfony($app, $env, true);

    sfLogManager::rotate($app, $env, sfConfig::get('sf_logging_period'), sfConfig::get('sf_logging_history'), true);
  }
}
