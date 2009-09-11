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
  protected
    $model = 'MailMessage';

  /**
   * Sets the model class name associated with this transport.
   *
   * @param string $model The model class name
   */
  public function setModel($model)
  {
    $this->model = $model;
  }

  /**
   * Gets the model class name associated with this transport.
   *
   * @return string The model class name
   */
  public function getModel()
  {
    return $this->model;
  }

  /**
   * Stores a message in the queue.
   *
   * @param Swift_Mime_Message $message The message to store
   */
  public function store(Swift_Mime_Message $message)
  {
    $object = new $this->model;

    if (!$object instanceof sfMailerModelInterface)
    {
      throw new InvalidArgumentException('The mailer message object must implement the sfMailerModelInterface interface.');
    }

    if (!$object instanceof Doctrine_Record)
    {
      throw new InvalidArgumentException('The mailer message object must be a Doctrine_Record object.');
    }

    $object->setMessage($message);
    $object->save();
  }

  /**
   * Sends a message using the given transport instance.
   *
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   * @param int             $max               The maximum number of messages to send
   *
   * @return int The number of sent emails
   */
  public function doSend(Swift_Transport $transport, &$failedRecipients = null, $max = 0)
  {
    $count = 0;
    $messages = array();

    $table = Doctrine::getTable($this->model);
    if (method_exists($table, $method = 'getQueueQuery'))
    {
      $query = $table->$method();
    }
    else
    {
      $query = $table->createQuery();
    }

    if ($max)
    {
      $query->setLimit($max);
    }

    foreach ($query->execute() as $object)
    {
      $message = $object->getMessage();

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
