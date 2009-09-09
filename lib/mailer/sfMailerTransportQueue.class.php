<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerTransportQueue is a SwiftMailer transport that stores messages for later sending.
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfMailerTransportQueue implements Swift_Transport
{
  protected $eventDispatcher = null;

  /**
   * Creates a new Doctrine transport.
   */
  public function __construct()
  {
    $this->eventDispatcher = Swift_DependencyContainer::getInstance()->lookup('transport.eventdispatcher');
  }

  /**
   * Test if this Transport mechanism has started.
   *
   * @return boolean
   */
  public function isStarted()
  {
    return true;
  }

  /**
   * Starts this Transport mechanism.
   */
  public function start()
  {
  }

  /**
   * Stops this Transport mechanism.
   */
  public function stop()
  {
  }

  /**
   * Sends the given message.
   *
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   *
   * @return int The number of sent emails
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $this->store($message, $failedRecipients);

    $evt = $this->eventDispatcher->createSendEvent($this, $message);
    $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
    $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');

    return 0;
  }

  /**
   * Stores a message in the queue.
   *
   * @param Swift_Mime_Message $message The message to store
   */
  abstract public function store(Swift_Mime_Message $message);

  /**
   * Sends a message using the given transport instance.
   *
   * The return value is the number of recipients who were accepted for delivery.
   *
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   * @param int             $max               The maximum number of messages to send
   *
   * @return int The number of sent emails
   */
  abstract public function doSend(Swift_Transport $transport, &$failedRecipients = null, $max = 0);

  /**
   * Register a plugin in the Transport.
   *
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->eventDispatcher->bindEventListener($plugin);
  }
}
