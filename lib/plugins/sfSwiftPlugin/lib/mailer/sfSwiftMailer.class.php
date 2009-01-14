<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2009 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSwiftMailer provides methods for creating and sending emails with the Swift library.
 *
 * http://swiftmailer.org/wikidocs/
 * http://www.swiftmailer.org/api/php5/index.html
 *
 * Swift_Connection_NativeMail is the connection driver using the native PHP mail() function. (default)
 * Swift_Connection_SMTP sends mail via a SMTP server. The constructor takes three parameters, all of which are optional. (gmail example below)
 * Swift_Connection_Sendmail uses a sendmail binary to send the mail. You can specify its path to the constructor.
 * Swift_Connection_Multi is the first special driver, that can be used to combine more than one connection driver. It provides redundancy in the event that a SMTP server is unavailable at the time of the request. The constructor takes an array of Swift_Connection object instances. (not supported yet)
 * Swift_Connection_Rotator is the last one, doing a bit more than Swift_Connection_Multi by keeping track of down servers, and managing rotation of "alive" servers. How to use it is beyond the scope of this cookbook recipe, and you should refer to the Swift Mailer documentation. (not supported yet)
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfSwiftMailer.class.php 9091 2008-05-20 07:19:07Z dwhittle $
 */
class sfSwiftMailer extends sfMailer
{
  protected
    $dispatcher    = null,
    $swift         = null;

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, Swift $swift, $options = array())
  {
    $this->initialize($dispatcher, $swift, $options);
    
    if ($this->options['auto_shutdown'])
    {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  /**
   * Initializes this sfSwiftMailer.
   *
   * Available options:
   *  * logging: Whether to enable logging or not (false by default)
   *  * cache: The cache method (memory | disk)
   *  * charset: The default message charset
   *  * culture: The default message culture
   *  * from_email: The default from email address
   *
   * @param  sfEventDispatcher    $dispatcher      A sfEventDispatcher instance
   * @param  Swift                $swift           A Swift mailer instance
   * @param  array                $options         An associative array of options
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, Swift $swift, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->swift = $swift;
    
    $this->options = array_merge(array(
      'auto_shutdown' => true,
      'logging'       => false,
      'from_email'    => 'webmaster@localhost.localdomain',
      'cache'         => 'memory',
      'charset'       => 'utf8',
      'culture'       => 'en',
    ), $options);
    
    try {
      if($this->options['cache'] == 'disk')
      {
        Swift_CacheFactory::setClassName('Swift_Cache_Disk');
        if(isset($this->options['cache_dir']))
        {
          Swift_Cache_Disk::setSavePath($this->options['cache_dir']);
        }
      }
      elseif($this->options['cache'] == 'memory')
      {
        Swift_CacheFactory::setClassName('Swift_Cache_Memory');
      }
      
      if($this->options['logging'])
      {
        /**
         * @todo add shim logger for symfony logging
         */
        // Swift_LogContainer::setLog('Swift_Log_Symfony');
      }
    }
    catch(Exception $e)
    {
      throw sfInitializationException::createFromException($e);
    }

    $this->dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));
    $this->dispatcher->connect('email.send', array($this, 'listenToEmailSendEvent'));
  }

  /**
   * Sends email with the mailer.
   *
   * @param  string $to             The email address to send email to.
   * @param  string $subject        The subject for the email.
   * @param  string $body           The body of the email.
   * @param  string $attachements   The attachments for the email.
   *
   * @return boolean true if sent, otherwise throws exception
   *
   * @throws <b>Exception</b> If an error occurs while sending email with this mailer
   */
  public function send($to, $subject, $body, $attachments = null, $options = null)
  {
    $options = is_array($options) ? array_merge($this->options, $options) : $this->options;
    
    $recipients = new Swift_RecipientList();
    $to = is_array($to) ? $to : array($to);
    foreach($to as $recipient)
    {
      $recipients->addTo($recipient);
    }

    // message
    $message = new Swift_Message($subject);
    $message->setCharset($options['charset']);
    $message->headers->setLanguage($options['culture']);
    
    $message->attach(new Swift_Message_Part($body, 'text/html'));
    $message->attach(new Swift_Message_Part($body, 'text/plain'));

    // attachments
    if(!is_null($attachments))
    {
      $finfo = finfo_open(FILEINFO_MIME);
      foreach($attachments as $attachment)
      {
        if(is_readable($attachement))
        {
          $message->attach(new Swift_Message_Attachment(new Swift_File($attachement), basename($attachement), finfo_file($finfo, $attachement))); 
        }
      }
    }

    // send
    $this->swift->send($message, $recipients, $options['from_email']);
    
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Sending email to %s', implode('", "', is_array($to) ? $to : array($to))))));
    }
    
    return true;
  }

  /**
   * Listens to the email.send event.
   *
   * @param sfEvent $event  An sfEvent instance
   *
   */
  public function listenToEmailSendEvent(sfEvent $event)
  {
    if(isset($event['to']) && isset($event['subject']) && isset($event['body']))
    {
      $this->send($event['to'], $event['subject'], $event['body'], isset($event['attachments']) ? $event['attachments'] : null); 
    }
  }
  
  /**
   * Listens to the user.change_culture event.
   *
   * @param sfEvent $event  An sfEvent instance
   *
   */
  public function listenToChangeCultureEvent(sfEvent $event)
  {
    $this->options['culture'] = $event['culture'];
  }
  
  /**
   * Returns the event dispatcher.
   *
   * @return sfEventDispatcher The sfEventDispatcher instance
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Sets the event dispatcher.
   * 
   * @param sfEventDispatcher $dispatcher The event dispatcher
   */  
  public function setEventDispatcher(sfEventDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }
  

  /**
   * Returns the swift mailer instance.
   *
   * @return Swift The Swift instance
   */  
  public function getSwift()
  {
    return $this->swift;
  }

  /**
   * Sets the swift mailer instance.
   * 
   * @param Swift $swift The Swift instance
   */    
  public function setSwift(Swift $swift)
  {
    $this->swift = $swift;
  }

  /**
   * Executes the shutdown procedure.
   */
  public function shutdown()
  {
    if(isset($this->swift))
    {
      $this->swift->disconnect(); 
    }
  }
}
