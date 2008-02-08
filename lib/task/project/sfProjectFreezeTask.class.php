<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Freezes symfony libraries.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectFreezeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('symfony_data_dir', sfCommandArgument::REQUIRED, 'The symfony data directory'),
    ));

    $this->aliases = array('freeze');
    $this->namespace = 'project';
    $this->name = 'freeze';
    $this->briefDescription = 'Freezes symfony libraries';

    $this->detailedDescription = <<<EOF
The [project:freeze|INFO] task copies all the symfony core files to
the current project:

  [./symfony project:freeze|INFO]

The task also changes [config/config.php|COMMENT] to switch to the
embedded symfony files.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // check that the symfony librairies are not already freeze for this project
    if (is_readable(sfConfig::get('sf_lib_dir').'/symfony'))
    {
      throw new sfCommandException('You can only freeze when lib/symfony is empty.');
    }

    if (is_readable(sfConfig::get('sf_data_dir').'/symfony'))
    {
      throw new sfCommandException('You can only freeze when data/symfony is empty.');
    }

    if (is_readable(sfConfig::get('sf_web_dir').'/sf'))
    {
      throw new sfCommandException('You can only freeze when web/sf is empty.');
    }

    if (is_link(sfConfig::get('sf_web_dir').'/sf'))
    {
      $this->getFilesystem()->remove(sfConfig::get('sf_web_dir').'/sf');
    }

    $symfony_lib_dir  = sfConfig::get('sf_symfony_lib_dir');
    $symfony_data_dir = $arguments['symfony_data_dir'];

    $this->logSection('freeze', sprintf('freezing lib found in "%s', $symfony_lib_dir));
    $this->logSection('freeze', sprintf('freezing data found in "%s"', $symfony_data_dir));

    $this->getFilesystem()->mkdirs('lib'.DIRECTORY_SEPARATOR.'symfony');
    $this->getFilesystem()->mkdirs('data'.DIRECTORY_SEPARATOR.'symfony');

    $finder = sfFinder::type('any')->ignore_version_control();
    $this->getFilesystem()->mirror($symfony_lib_dir, sfConfig::get('sf_lib_dir').'/symfony', $finder);
    $this->getFilesystem()->mirror($symfony_data_dir, sfConfig::get('sf_data_dir').'/symfony', $finder);

    $this->getFilesystem()->rename(sfConfig::get('sf_data_dir').'/symfony/web/sf', sfConfig::get('sf_web_dir').'/sf');

    // change symfony paths in config/config.php
    file_put_contents('config/config.php.bak', $symfony_lib_dir);
    $this->changeSymfonyDirs("dirname(__FILE__).'/../lib/symfony'");
  }

  protected function changeSymfonyDirs($symfony_lib_dir)
  {
    $content = file_get_contents('config/config.php');
    $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
    file_put_contents('config/config.php', $content);
  }
}
