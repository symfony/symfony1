<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once('phing/Phing.php');

/**
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPhing.class.php 9924 2008-06-27 11:14:39Z fabien $
 */
class sfPhing extends Phing
{
  function printVersion()
  {
    print(self::getPhingVersion()."\n");
  }

  function getPhingVersion()
  {
    return 'sfPhing';
  }

  public static function shutdown($exitcode = 0)
  {
    self::getTimer()->stop();

    throw new Exception(sprintf('Problem executing Phing task (%s).', $exitcode));
  }
}
