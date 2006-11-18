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
class sfFileLogger
{
  protected
    $fp = null;

  public function initialize($options = array())
  {
    if (!isset($options['file']))
    {
      throw new sfConfigurationException('File option is mandatory for a file logger');
    }

    if (!is_dir(dirname($options['file'])))
    {
      mkdir($options['file'], 0777, 1);
    }

    $this->fp = fopen($options['file'], 'a');
  }

  public function log($message, $priority, $priorityName)
  {
    $line = sprintf("%s %s [%s] %s%s", strftime('%b %d %H:%M:%S'), 'symfony', $priorityName, $message, strstr(PHP_OS, 'WIN') ? "\r\n" : "\n");

    flock($this->fp, LOCK_EX);
    fwrite($this->fp, $line);
    flock($this->fp, LOCK_UN);
  }

  public function shutdown()
  {
    if ($this->fp)
    {
      fclose($this->fp);
    }
  }
}
