<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerFileTransportQueue is a SwiftMailer transport that the filesystem as a queue.
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailerFileTransportQueue extends sfMailerTransportQueue
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * path: The path where to store the messages
   */
  public function __construct($options = array())
  {
    if (!isset($options['path']))
    {
      throw new InvalidArgumentException('You must provide a "path" options when using the message file queue.');
    }

    parent::__construct($options);
  }

  /**
   * Stores a message in the queue.
   *
   * @param Swift_Mime_Message $message The message to store
   */
  public function store(Swift_Mime_Message $message)
  {
    $ser = serialize($message);

    if (!file_exists($this->options['path']))
    {
      mkdir($this->options['path'], 0777, true);
    }

    file_put_contents($this->options['path'].'/'.md5($ser.uniqid()).'.message', $ser);
  }

  /**
   * Sends messages using the given transport instance.
   *
   * Available options:
   *
   *  * time: The maximum time allowed to send emails
   *
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   * @param array           $options           An array of options
   *
   * @return int The number of sent emails
   */
  public function doSend(Swift_Transport $transport, &$failedRecipients = null, $options = array())
  {
    $count = 0;
    $messages = array();
    $options = array_merge($this->options, $options);

    if (!file_exists($options['path']))
    {
      return 0;
    }

    if (isset($options['time']))
    {
      $begin = time();
    }

    foreach (new DirectoryIterator($options['path']) as $file)
    {
      if (isset($options['time']) && time() - $begin > $options['time'])
      {
        break;
      }

      $file = $file->getRealPath();

      if (!strpos($file, '.message'))
      {
        continue;
      }

      $message = unserialize(file_get_contents($file));
      unlink($file);

      try
      {
        $count += $transport->send($message, $failedRecipients);
      }
      catch (Exception $e)
      {
        // TODO: What to do with errors?
      }
    }

    return $count;
  }
}
