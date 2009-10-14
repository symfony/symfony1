<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrades Propel.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    if (
      file_exists($old = sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php')
      &&
      !file_exists($new = sfConfig::get('sf_lib_dir').'/filter/BaseFormFilterPropel.class.php')
    )
    {
      $this->getFilesystem()->rename($old, $new);
    }
  }
}
