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

    $this->addOptions(array(
      new sfCommandOption('history', null, sfCommandOption::PARAMETER_REQUIRED, 'The maximum number of old log files to keep', 10),
      new sfCommandOption('period', null, sfCommandOption::PARAMETER_REQUIRED, 'The period in days', 7),
    ));

    $this->aliases = array('log-rotate');
    $this->namespace = 'log';
    $this->name = 'rotate';
    $this->briefDescription = 'Rotates an application log files';

    $this->detailedDescription = <<<EOF
The [log:rotate|INFO] task rotates application log files for a given
environment:

  [./symfony log:rotate frontend dev|INFO]

You can specify a [period|COMMENT] or a [history|COMMENT] option:

  [./symfony --history=10 --period=7 log:rotate frontend dev|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $arguments['env'];

    sfLogManager::rotate($app, $env, $options['period'], $options['history'], true);
  }
}
