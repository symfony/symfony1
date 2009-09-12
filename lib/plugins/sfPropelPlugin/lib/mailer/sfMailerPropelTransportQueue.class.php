<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerPropelTransportQueue is a SwiftMailer transport that uses a Propel model as a queue.
 *
 * Example schema:
 *
 *  mail_message:
 *   message:    { type: clob }
 *   created_at: ~
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailerPropelTransportQueue extends sfMailerTransportQueue
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

    if (!$object instanceof BaseObject)
    {
      throw new InvalidArgumentException('The mailer message object must be a BaseObject object.');
    }

    $model = constant($this->options['model'].'::PEER');
    $method = 'set'.call_user_func(array($model, 'translateFieldName'), $variable, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME);

    $object->$method(serialize($message));
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
    $model = constant($this->options['model'].'::PEER');

    if (isset($options['method']))
    {
      $method = $options['method'];

      $objects = call_user_func(array($model, $method), $options);
    }
    else
    {
      $objects = call_user_func(array($model, 'doSelect'), new Criteria());
    }

    if (isset($options['time']))
    {
      $begin = time();
    }

    $method = 'get'.call_user_func(array($model, 'translateFieldName'), $variable, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME);
    foreach ($objects as $object)
    {
      if (isset($options['time']) && time() - $begin > $options['time'])
      {
        break;
      }

      $message = unserialize($object->$method());

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
