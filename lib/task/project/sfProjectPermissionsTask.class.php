<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Fixes symfony directory permissions.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectPermissionsTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('permissions');
    $this->namespace = 'project';
    $this->name = 'permissions';
    $this->briefDescription = 'Fixes symfony directory permissions';

    $this->detailedDescription = <<<EOF
The [project:permissions|INFO] task fixes directory permissions:

  [./symfony project:permissions|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->filesystem->chmod(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_cache_dir_name'), 0777);
    $this->filesystem->chmod(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_log_dir_name'), 0777);
    $this->filesystem->chmod(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_web_dir_name').DIRECTORY_SEPARATOR.sfConfig::get('sf_upload_dir_name'), 0777);
    $this->filesystem->chmod(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony', 0777);

    $dirs = array(sfConfig::get('sf_cache_dir_name'), sfConfig::get('sf_web_dir_name').DIRECTORY_SEPARATOR.sfConfig::get('sf_upload_dir_name'), sfConfig::get('sf_log_dir_name'));
    $dirFinder = sfFinder::type('dir')->ignore_version_control();
    $fileFinder = sfFinder::type('file')->ignore_version_control();
    foreach ($dirs as $dir)
    {
      $this->filesystem->chmod($dirFinder->in($dir), 0777);
      $this->filesystem->chmod($fileFinder->in($dir), 0666);
    }
  }
}
