<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2009 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailer allows you to customize the way symfony sends email.
 *
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfMailer.class.php 9091 2008-05-20 07:19:07Z dwhittle $
 */
abstract class sfMailer
{
  protected $options = array();
   
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
   * @throws <b>sfMailerException</b> If an error occurs while sending email with this mailer
   */
  abstract public function send($to, $subject, $body, $attachments = null, $options = null);
  
  /**
   * Returns the options.
   */
  public function getOptions()
  {
    return $this->options;
  }
  
  /**
   * Returns an option.
   */
  public function getOption($option)
  {
    return isset($this->options[$option]) ? $this->options[$option] : null;
  }  
  
  /**
   * Sets an option.
   */
  public function setOption($option, $value)
  {
    $this->options[$option] = $value;
  }
}
