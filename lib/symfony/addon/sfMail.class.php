<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfMail class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfMail.class.php 466 2005-09-17 19:03:25Z fabien $
 */
abstract class sfMail
{
  public function newInstance($name)
  {
    $class = 'sfMail_'.$name;
    require_once('symfony/addon/sfMail/'.$name.'.class.php');
    if (class_exists($class))
    {
      try
      {
        $object = new $class();
        if (!($object instanceof sfMail))
        {
          // the class name is of the wrong type
          $error = 'Class "%s" is not of the type sfMail';
          $error = sprintf($error, $class);

          throw new Exception($error);
        }

        return $object;
      }
      catch (Exception $e)
      {
        throw new Exception($e);
      }
    }
    else
    {
      // the class doesn't exist
      $error = 'Nonexistent sfMail implementation: %s';
      $error = sprintf($error, $class);

      throw new Exception($error);
    }
  }

  abstract public function initialize();

  abstract public function setSubject($value);
  abstract public function getSubject();
  abstract public function setBody($value);
  abstract public function getBody();
  abstract public function setFrom($email, $name = '');
  abstract public function getFrom();
  abstract public function setSender($value);
  abstract public function getSender();
/*
  abstract public function setPriority($value);
  abstract public function getPriority();
  abstract public function setEncoding($value);
  abstract public function getEncoding();
  abstract public function setAltBody($value);
  abstract public function getAltBody();
  abstract public function setWordWrap($value);
  abstract public function getWordWrap();
  abstract public function setHostname($value);
  abstract public function getHostname();
  abstract public function setHost($value);
  abstract public function getHost();
  abstract public function setPort($value);
  abstract public function getPort();
  abstract public function setSmtpAuth($value);
  abstract public function getSmtpAuth();
  abstract public function setUsername($value);
  abstract public function getUsername();
  abstract public function setPassword($value);
  abstract public function getPassword();
  abstract public function setSmtpDebug($value);
  abstract public function getSmtpDebug();
  abstract public function setSmtpKeepAlive($value);
  abstract public function getSmtpKeepAlive();
  abstract public function setHtml();
*/

  abstract public function setCharset($charset);
  abstract public function getCharset();
  abstract public function setContentType($value);
  abstract public function getContentType();

  abstract public function setMailer($type = 'php');

  abstract public function addAddress($address, $name = '');
  abstract public function addCc($address, $name = '');
  abstract public function addBcc($address, $name = '');
  abstract public function addReplyTo($address, $name = '');

  abstract public function clearAddresses();
  abstract public function clearCcs();
  abstract public function clearBccs();
  abstract public function clearReplyTos();
  abstract public function clearAllRecipients();

  abstract public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream');
  abstract public function addStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream');
  abstract public function addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream');
  abstract public function clearAttachments();

  abstract function addCustomHeader($name, $value);
  abstract function clearCustomHeaders();

  abstract public function send();
}

?>