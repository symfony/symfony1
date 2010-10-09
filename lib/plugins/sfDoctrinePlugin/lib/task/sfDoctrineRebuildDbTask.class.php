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
 * Creates database for current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRebuildDbTask.class.php 14213 2008-12-19 21:03:13Z Jonathan.Wage $
 */
class sfDoctrineRebuildDbTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Whether to no-confirmation dropping of the database')
    ));

    $this->aliases = array('doctrine-rebuild-db');
    $this->namespace = 'doctrine';
    $this->name = 'rebuild-db';
    $this->briefDescription = 'Creates database for current model';

    $this->detailedDescription = <<<EOF
The [doctrine:rebuild-db|INFO] task creates the database:

  [./symfony doctrine:rebuild-db|INFO]

The task read connection information in [config/doctrine/databases.yml|COMMENT]:
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $dropDb = new sfDoctrineDropDbTask($this->dispatcher, $this->formatter);
    $dropDb->setCommandApplication($this->commandApplication);

    $dropDbOptions = array();
    if (!empty($options['application']))
    {
      $dropDbOptions[] = '--application=' . $options['application'];
    }
    $dropDbOptions[] = '--env='.$options['env'];
    if (isset($options['no-confirmation']) && $options['no-confirmation'])
    {
      $dropDbOptions[] = '--no-confirmation';
    }

    $dropDb->run(array(), $dropDbOptions);

    $buildAllOptions = array();
    if (!empty($options['application']))
    {
      $buildAllOptions[] = '--application=' . $options['application'];
    }
    $buildAllOptions[] = '--env='.$options['env'];

    $buildAll = new sfDoctrineBuildAllTask($this->dispatcher, $this->formatter);
    $buildAll->setCommandApplication($this->commandApplication);
    $buildAll->run(array(), array('--env='.$options['env']));
  }
}