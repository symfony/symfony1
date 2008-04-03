<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Executes the optimizer task.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfOptimizerTask.class.php 4855 2007-08-10 07:36:48Z dwhittle $
 */
class sfCacheOptimizeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('app', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('env', sfCommandArgument::OPTIONAL, 'The environment name', 'prod'),
    ));

    $this->aliases = array('optimize');
    $this->namespace = 'cache';
    $this->name = 'optimize';
    $this->briefDescription = 'Optimizes symfony cache files for production environment';
    $this->detailedDescription = <<<EOF
The [optimize:cache] task optimizes cache files for production environment:

  [./symfony optimize:cache application prod]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if(count($arguments) < 2)
    {
      throw new sfCommandException('You must provide an application and an environment.');
    }

    if(!in_array($arguments['env'], array('prod')))
    {
      throw new sfCommandException('You must provide a valid environment (prod).');
    }

    $config = sfConfig::get('sf_app_cache_dir').'/config/config_core_compile.yml.php';
    if(!is_readable($config))
    {
      $cacheCreate = new sfCacheGenerateTask($this->dispatcher, $this->formatter);
      $cacheCreate->run(array('app' => $arguments['app'], 'env' => $arguments['env']));
    }

    $optimizer = new sfOptimizer(file_get_contents($config));
    $optimizer->registerStandardOptimizers();
    file_put_contents($config, $optimizer->optimize());

    $this->logSection('cache', sprintf('optimized cache for application "%s" in environment "%s"', $arguments['app'], $arguments['env']));
  }
}
