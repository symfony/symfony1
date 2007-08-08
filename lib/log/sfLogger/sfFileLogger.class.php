<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFileLogger logs messages in a file.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFileLogger extends sfLogger
{
  protected
    $fp = null;

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - file: The file path or a php wrapper to log messages
   *         You can use any support php wrapper. To write logs to the Apache error log, use php://stderr
   *
   * @param array Options for the logger
   */
  public function initialize($options = array())
  {
    if (!isset($options['file']))
    {
      throw new sfConfigurationException('You must provide a "file" parameter for this logger.');
    }

    $dir = dirname($options['file']);
    if (!is_dir($dir))
    {
      mkdir($dir, 0777, true);
    }

    if (!is_writable($dir) || (file_exists($options['file']) && !is_writable($options['file'])))
    {
      throw new sfFileException(sprintf('Unable to open the log file "%s" for writing.', $options['file']));
    }

    $this->fp = fopen($options['file'], 'a');

    return parent::initialize($options);
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  protected function doLog($message, $priority)
  {
    flock($this->fp, LOCK_EX);
    fwrite($this->fp, sprintf("%s %s [%s] %s%s", strftime('%b %d %H:%M:%S'), 'symfony', sfLogger::getPriorityName($priority), $message, DIRECTORY_SEPARATOR == '\\' ? "\r\n" : "\n"));
    flock($this->fp, LOCK_UN);
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    if ($this->fp)
    {
      fclose($this->fp);
    }
  }
}
