<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Move symfony 1.0 error templates.
 *
 * @package    symfony
 * @subpackage task
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfExceptionTemplatesUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    // move the symfony 1.0 error 500 template out of the web directory
    $file = sfConfig::get('sf_web_dir').'/errors/error500.php';
    if (file_exists($file))
    {
      $this->getFilesystem()->mkdirs(sfConfig::get('sf_config_dir').'/error');
      $this->getFilesystem()->rename($file, sfConfig::get('sf_config_dir').'/error/error500.html.php');
    }
  }
}
