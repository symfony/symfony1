<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNoLogger is a noop logger.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfNoLogger extends sfLogger
{
  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  protected function doLog($message, $priority)
  {
  }
}
