<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrade configuration.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfConfigUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $finder = $this->getFinder('file')->name('config.php');
    foreach ($finder->in($this->getProjectConfigDirectories()) as $file)
    {
      $content = file_get_contents($file);
      $content = str_replace('sfCore::bootstrap($sf_symfony_lib_dir, $sf_symfony_data_dir)', 'sfCore::bootstrap($sf_symfony_lib_dir)', $content, $count);
      if ($count)
      {
        $this->logSection('config', sprintf('Migrating %s', $file));
        file_put_contents($file, $content);
      }
    }
  }
}
