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
   * - file:      The file path or a php wrapper to log messages
   *              You can use any support php wrapper. To write logs to the Apache error log, use php://stderr
   * - dir_mode:  The mode to use when creating a directory (default to 0777)
   * - file_mode: The mode to use when creating a file (default to 0666)
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance
   * @param  array        An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    if (!isset($options['file']))
    {
      throw new sfConfigurationException('You must provide a "file" parameter for this logger.');
    }

    $dir = dirname($options['file']);
    if (!is_dir($dir))
    {
      mkdir($dir, isset($options['dir_mode']) ? $options['dir_mode'] : 0777, true);
    }

    $fileExists = file_exists($options['file']);
    if (!is_writable($dir) || ($fileExists && !is_writable($options['file'])))
    {
      throw new sfFileException(sprintf('Unable to open the log file "%s" for writing.', $options['file']));
    }

    $this->fp = fopen($options['file'], 'a');
    if (!$fileExists)
    {
      chmod($options['file'], isset($options['file_mode']) ? $options['file_mode'] : 0666);
    }

    return parent::initialize($dispatcher, $options);
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
