<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailer is the main entry point for the mailer system.
 *
 * This class is instanciated by sfContext on demand.
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailer extends Swift_Mailer
{
  protected
    $logger  = null,
    $options = array();

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * charset: The default charset to use for messages
   *  * logging: Whether to enable logging
   *  * delivery_strategy: The delivery strategy to use
   *  * queue_class: The queue transport class (for the queue strategy)
   *  * queue_options: The options to pass to the queue constructor
   *  * delivery_address: The email address to use for the single_address strategy
   *  * transport: The main transport configuration
   *  *   * class: The main transport class
   *  *   * param: The main transport parameters
   *
   * @param sfEventDispatcher $dispatcher An event dispatcher instance
   * @param array             $options    An array of options
   */
  public function __construct(sfEventDispatcher $dispatcher, $options)
  {
    // options
    $this->options = array_merge(array(
      'charset' => 'UTF-8',
      'logging' => false,
      'delivery_strategy' => 'realtime',
      'transport' => array(
        'class' => 'Swift_MailTransport',
        'param' => array(),
       ),
    ), $options);

    $this->options['delivery_strategy'] = sfMailerTransport::validateDeliveryStrategy($this->options['delivery_strategy']);

    // transport
    $class = $this->options['transport']['class'];
    $transport = new $class();
    if (isset($this->options['transport']['param']))
    {
      foreach ($this->options['transport']['param'] as $key => $value)
      {
        $method = 'set'.ucfirst($key);
        if (method_exists($transport, $method))
        {
          $transport->$method($value);
        }
      }
    }

    $queue = null;
    if (sfMailerTransport::QUEUE == $this->options['delivery_strategy'])
    {
      if (!isset($this->options['queue_class']))
      {
        throw new InvalidArgumentException('For the queue mail delivery strategy, you must also define a queue_class option');
      }
      $options = isset($this->options['queue_options']) ? $this->options['queue_options'] : array();

      $queue = new $this->options['queue_class']($options);
    }

    $transport = new sfMailerTransport($this->options['delivery_strategy'], $transport, $queue);

    if (sfMailerTransport::SINGLE_ADDRESS == $this->options['delivery_strategy'])
    {
      if (!isset($this->options['delivery_address']))
      {
        throw new InvalidArgumentException('For the single_address mail delivery strategy, you must also define a delivery_address option');
      }

      $transport->setDeliveryAddress($this->options['delivery_address']);
    }

    // logger
    if ($this->options['logging'])
    {
      $transport->setLogger(new sfMailerMessageLoggerPlugin($dispatcher));
    }

    // preferences
    Swift_Preferences::getInstance()->setCharset($this->options['charset']);

    parent::__construct($transport);

    $dispatcher->notify(new sfEvent($this, 'mailer.configure'));
  }

  /**
   * Creates a new message.
   *
   * @param string|array $from    The from address
   * @param string|array $to      The recipient(s)
   * @param string       $subject The subject
   * @param string       $body    The body
   *
   * @return Swift_Message A Swift_Message instance
   */
  public function compose($from = null, $to = null, $subject = null, $body = null)
  {
    return sfMailerMessage::newInstance()
      ->setFrom($from)
      ->setTo($to)
      ->setSubject($subject)
      ->setBody($body)
    ;
  }

  /**
   * Sends a message.
   *
   * @param string|array $from    The from address
   * @param string|array $to      The recipient(s)
   * @param string       $subject The subject
   * @param string       $body    The body
   *
   * @return int The number of sent emails
   */
  public function composeAndSend($from, $to, $subject, $body)
  {
    return $this->send($this->compose($from, $to, $subject, $body));
  }

  /**
   * Send the current queued mails.
   *
   * The return value is the number of recipients who were accepted for delivery.
   *
   * @param array    $options The options to pass to the queue
   * @param string[] &$failedRecipients An array of failures by-reference
   *
   * @return int The number of sent emails
   */
  public function sendQueue($options = array(), &$failedRecipients = null)
  {
    if (!$this->getTransport()->getTransportQueue())
    {
      throw new LogicException('You cannot send mails in queue if no mailer transport queue is defined.');
    }

    $transport = $this->getTransport()->getTransport();

    if (!$transport->isStarted())
    {
      $transport->start();
    }

    return $this->getTransport()->getTransportQueue()->doSend($transport, $failedRecipients, $options);
  }

  /**
   * Forces the next call to send() to use the realtime strategy.
   *
   * @return sfMailer The current sfMailer instance
   */
  public function sendNextImmediately()
  {
    $this->getTransport()->sendNextImmediately();

    return $this;
  }

  static public function initialize()
  {
    require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/swift_init.php';
  }
}
