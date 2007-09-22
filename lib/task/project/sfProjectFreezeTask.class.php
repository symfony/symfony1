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
 * @subpackage command
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
    // Check that the symfony librairies are not already freeze for this project
    if (is_readable('lib/symfony'))
    {
      throw new sfCommandException('You can only freeze when lib/symfony is empty.');
    }

    if (is_readable('data/symfony'))
    {
      throw new sfCommandException('You can only freeze when data/symfony is empty.');
    }

    if (is_readable('web/sf'))
    {
      throw new sfCommandException('You can only freeze when web/sf is empty.');
    }

    if (is_link('web/sf'))
    {
      $this->filesystem->remove('web/sf');
    }

    $symfony_lib_dir  = sfConfig::get('sf_symfony_lib_dir');
    $symfony_data_dir = sfConfig::get('sf_symfony_data_dir');

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('freeze', 'freezing lib found in "'.$symfony_lib_dir.'"'))));
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('freeze', 'freezing data found in "'.$symfony_data_dir.'"'))));

    $this->filesystem->mkdirs('lib'.DIRECTORY_SEPARATOR.'symfony');
    $this->filesystem->mkdirs('data'.DIRECTORY_SEPARATOR.'symfony');

    $finder = sfFinder::type('any')->ignore_version_control();
    $this->filesystem->mirror($symfony_lib_dir, 'lib/symfony', $finder);
    $this->filesystem->mirror($symfony_data_dir, 'data/symfony', $finder);

    $this->filesystem->rename('data/symfony/web/sf', 'web/sf');

    // Change symfony paths in config/config.php
    file_put_contents('config/config.php.bak', "$symfony_lib_dir#$symfony_data_dir");
    $this->changeSymfonyDirs("dirname(__FILE__).'/../lib/symfony'", "dirname(__FILE__).'/../data/symfony'");

    // Install the command line
    $this->filesystem->copy($symfony_data_dir.'/bin/symfony.php', 'symfony.php');
  }

  protected function changeSymfonyDirs($symfony_lib_dir, $symfony_data_dir)
  {
    $content = file_get_contents('config/config.php');
    $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
    $content = preg_replace("/^(\s*.sf_symfony_data_dir\s*=\s*).+?;/m", "$1$symfony_data_dir;", $content);
    file_put_contents('config/config.php', $content);
  }
}
