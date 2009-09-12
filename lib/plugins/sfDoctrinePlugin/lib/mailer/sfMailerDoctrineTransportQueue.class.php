<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerDoctrineTransportQueue is a SwiftMailer transport that uses a Doctrine model as a queue.
 *
 * Example schema:
 *
 *  MailMessage:
 *   actAs: { Timestampable: ~ }
 *   columns:
 *     message: { type: clob, notnull: true }
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailerDoctrineTransportQueue extends sfMailerTransportQueue
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * model:  The Doctrine model to use to store the messages (MailMessage by default)
   *  * column: The column name to use for message storage (message by default)
   *  * method: The method to call to retrieve the messages to send (optional)
   */
  public function __construct($options = array())
  {
    parent::__construct(array_merge(array(
      'model'  => 'MailMessage',
      'column' => 'message',
    ), $options));
  }

  /**
   * Stores a message in the queue.
   *
   * @param Swift_Mime_Message $message The message to store
   */
  public function store(Swift_Mime_Message $message)
  {
    $object = new $this->options['model'];

    if (!$object instanceof Doctrine_Record)
    {
      throw new InvalidArgumentException('The mailer message object must be a Doctrine_Record object.');
    }

    $object->{$this->options['column']} = serialize($message);
    $object->save();
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
    $table = Doctrine::getTable($this->options['model']);

    if (isset($options['method']))
    {
      $method = $options['method'];

      $objects = $table->$method($options);
    }
    else
    {
      $objects = $table->createQuery()->execute();
    }

    if (isset($options['time']))
    {
      $begin = time();
    }

    foreach ($objects as $object)
    {
      if (isset($options['time']) && time() - $begin > $options['time'])
      {
        break;
      }

      $message = unserialize($object->{$this->options['column']});

      $object->delete();

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
