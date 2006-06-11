<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfNoLogger
{
  public function emerg() {}
  public function alert() {}
  public function crit() {}
  public function err() {}
  public function warning() {}
  public function notice() {}
  public function info() {}
  public function debug() {}
}
