<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrades config.php.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAutoloadingUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $phpFinder = $this->getFinder('file')->name('config.php');
    foreach ($phpFinder->in(glob(sfConfig::get('sf_root_dir').'/apps/*/config')) as $file)
    {
      $content = file_get_contents($file);
      if (false !== strpos($content, 'spl_autoload_register'))
      {
        continue;
      }
      $content .= <<<EOF

// insert your own autoloading callables here

if (sfConfig::get('sf_debug'))
{
  spl_autoload_register(array('sfAutoload', 'autoloadAgain'));
}
EOF;

      $this->log($this->formatSection('config.php', sprintf('Migrating %s', $file)));
      file_put_contents($file, $content);
    }
  }
}
