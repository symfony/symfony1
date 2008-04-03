<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates the symfony cache by simulating a web browser and requesting a list of URIs.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfCacheGenerateTask.class.php 4855 2007-08-10 07:36:48Z dwhittle $
 */
class sfCacheGenerateTask extends sfBaseTask
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

    $this->aliases = array('cache-generate', 'prefetch');
    $this->namespace = 'cache';
    $this->name = 'generate';
    $this->briefDescription = 'Generates the symfony cache for an application and environment';

    $this->detailedDescription = <<<EOF
The [cache:generate|INFO] task generates the symfony cache by simulating a web browser and requesting a list of URIs.

It can also be called with an application name and environment.

So, to generate the frontend application configuration for production environment:

  [./symfony cache:generate frontend prod|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (count($arguments) < 2)
    {
      throw new sfCommandException('You must provide an application and an environment.');
    }

    if(!in_array($arguments['env'], array('prod', 'dev', 'test')))
    {
      throw new sfCommandException('You must provide a valid environment (prod, dev, test).');
    }

    $this->checkAppExists($application);

    sfContext::createInstance($this->configuration);

    $uris = sfConfig::get('sf_prefetch_uris', array('/', '/not-found'));

    // simulate http requests to prime configuration/i18n/view cache
    $browser = new sfBrowser();
    foreach($uris as $uri)
    {
      $browser->get($uri);
    }

    // generate core_compile
    $this->configuration->getConfigCache()->checkConfig('config/core_compile.yml');

    $this->logSection('cache', sprintf('generated cache for application "%s" in environment "%s"', $arguments['app'], $arguments['env']));
  }
}
