<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoggerWrapper wraps a class that implements sfLoggerInterface into a real sfLogger object.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
calss sfLoggerWrapper extends sfLogger
{
  protected
    $logger = null;

  public function __construct(sfLoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  protected function doLog($message, $priority)
  {
    $this->logger->log($message, $priority);
  }
}
