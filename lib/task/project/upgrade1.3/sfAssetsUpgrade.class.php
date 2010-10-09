<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tries to fix the removal of the common filter.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfAssetsUpgrade.class.php 24395 2009-11-25 19:02:18Z Kris.Wallsmith $
 */
class sfAssetsUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $replace = 'common:'.PHP_EOL.'  class: sfCommonFilter';
    foreach (glob(sfConfig::get('sf_apps_dir').'/*/config/filters.yml') as $file)
    {
      $original = file_get_contents($file);
      $modified = preg_replace('/^common: +~/m', $replace, $original, -1, $count);

      if ($count)
      {
        $this->logSection('file+', $file);
        file_put_contents($file, $modified);
      }
    }
  }
}
