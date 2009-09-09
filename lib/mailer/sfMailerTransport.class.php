<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerTransport is the main transport used by sfMailer.
 *
 * It delegates transport to other transport objects, according to the strategy.
 *
 * This class supports the following strategy:
 *
 *  * realtime:       Messages are sent in realtime
 *  * queue:          Messages are queued for later sending
 *  * single_address: All messages are sent in realtime but to a single address
 *  * none:           Messages are just ignored
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailerTransport implements Swift_Transport
{
  const
    REALTIME       = 'realtime',
    QUEUE          = 'queue',
    SINGLE_ADDRESS = 'single_address',
    NONE           = 'none';

  protected
    $strategy        = null,
    $logger          = null,
    $force           = false,
    $deliveryAddress = null,
    $transport       = null,
    $transportQueue  = null,
    $eventDispatcher = null;

  /**
   * Creates a new Doctrine transport.
   */
  public function __construct($strategy, Swift_Transport $transport = null, sfMailerTransportQueue $transportQueue = null)
  {
    $this->strategy = self::validateDeliveryStrategy($strategy);
    $this->transport = $transport;
    $this->transportQueue = $transportQueue;

    $this->eventDispatcher = Swift_DependencyContainer::getInstance()->lookup('transport.eventdispatcher');
  }

  /**
   * Sets the current delivery address to use when the strategy is single_address.
   *
   * @param string $address The delivery address
   */
  public function setDeliveryAddress($address)
  {
    $this->deliveryAddress = $address;
  }

  /**
   * Gets the current delivery address to use when the strategy is single_address.
   *
   * @return string The delivery address
   */
  public function getDeliveryAddress()
  {
    return $this->deliveryAddress;
  }

  /**
   * Gets the current transport instance.
   *
   * @return sfMailerTransport The current transport instance.
   */
  public function getTransport()
  {
    return $this->transport;
  }

  /**
   * Gets the current transport queue instance.
   *
   * @return sfMailerTransportQueue The current transport queue instance.
   */
  public function getTransportQueue()
  {
    return $this->transportQueue;
  }

  /**
   * Gets the current delivery strategy.
   *
   * @return string The current delivery strategy.
   */
  public function getDeliveryStrategy()
  {
    return $this->strategy;
  }

  /**
   * Gets the current logger.
   *
   * @return The current logger instance
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Sets the logger.
   *
   * @param sfMailerMessageLoggerPlugin $logger The logger instance to use
   */
  public function setLogger(sfMailerMessageLoggerPlugin $logger)
  {
    $this->logger = $logger;

    $this->registerPlugin($logger);
  }

  /**
   * Forces to send the next message with the "real" transport instance.
   */
  public function sendNextImmediately()
  {
    $this->force = true;
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
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   *
   * @return int|false The number of sent emails, or false if the strategy is "none"
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    if (self::NONE == $this->strategy)
    {
      $evt = $this->eventDispatcher->createSendEvent($this, $message);
      $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
      $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');

      return false;
    }

    if (self::SINGLE_ADDRESS == $this->strategy)
    {
      self::rerouteMessageTo($message, $this->deliveryAddress);

      $transport = $this->transport;
    }
    elseif ($this->force || self::REALTIME == $this->strategy)
    {
      $transport = $this->transport;

      $this->force = false;
    }
    else
    {
      $transport = $this->transportQueue;
    }

    if (!$transport->isStarted())
    {
      $transport->start();
    }

    return $transport->send($message, $failedRecipients);
  }

  /**
   * Register a plugin in the Transport.
   *
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->eventDispatcher->bindEventListener($plugin);

    if ($this->transport)
    {
      $this->transport->registerPlugin($plugin);
    }

    if ($this->transportQueue)
    {
      $this->transportQueue->registerPlugin($plugin);
    }
  }

  /**
   * Changes the original receivers to the given address.
   *
   * The original addresses are stored as headers (X-Symfony-To, X-Symfony-Cc, and X-Symfony-Bcc).
   *
   * @param Swift_Mime_Message $message The message to reroute
   * @param string             $address The email address to use for rerouting
   */
  static public function rerouteMessageTo(Swift_Mime_Message $message, $address)
  {
    if ($message->getTo())
    {
      $message->getHeaders()->addTextHeader('X-Symfony-To', implode(', ', array_keys($message->getTo())));
    }
    if ($message->getCc())
    {
      $message->getHeaders()->addTextHeader('X-Symfony-Cc', implode(', ', array_keys($message->getCc())));
    }
    if ($message->getBcc())
    {
      $message->getHeaders()->addTextHeader('X-Symfony-Bcc', implode(', ', array_keys($message->getBcc())));
    }

    $message->setTo($address);
    $message->setBcc(array());
    $message->setCc(array());
  }

  /**
   * Validates a delivery strategy.
   *
   * @param string $string A delivery strategy
   *
   * @param string The delivery strategy
   */
  static public function validateDeliveryStrategy($strategy)
  {
    $st = @constant('sfMailerTransport::'.strtoupper($strategy));
    if (!$st)
    {
      throw new InvalidArgumentException(sprintf('Unkown mail delivery strategy "%s" (should be one of realtime, queue, single_address, or none)', $strategy));
    }

    return $st;
  }
}
