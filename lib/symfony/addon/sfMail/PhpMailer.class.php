<?php

require_once 'symfony/addon/sfMail/phpmailer/class.phpmailer.php';
require_once 'symfony/addon/sfMail/phpmailer/class.smtp.php';

class sfMail_PhpMailer extends sfMail {
    protected $mailer;

    public function initialize() {
        $this->mailer = new PHPMailer();
    }

    public function setCharset($charset)
    {
		$this->mailer->CharSet = $charset;
	}

    public function getCharset()
    {
		return $this->mailer->CharSet;
	}

    public function setContentType($content_type)
    {
		$this->mailer->ContentType = $content_type;
	}

    public function getContentType()
    {
		return $this->mailer->ContentType;
	}

    public function setSubject($subject)
    {
		$this->mailer->Subject = $subject;
	}

    public function getSubject()
    {
		return $this->mailer->Subject;
	}

    public function setBody($body)
    {
		$this->mailer->Body = $body;
	}

    public function getBody()
    {
		return $this->mailer->Body;
	}

    public function setType($type = 'plain') {
        if ($type == 'html') {
            $this->mailer->IsHTML(1);
        } else {
            $this->mailer->IsHTML(0);
        }
    }

    public function setMailer($type = 'php') {
        switch($type) {
            case 'smtp':
                $this->mailer->IsSMTP();
                break;
            case 'sendmail':
                $this->mailer->IsSendmail();
                break;
            default:
                $this->mailer->IsMail();
                break;
        }
    }

    public function setSender($address) {
        $this->mailer->Sender = $address;
    }

    public function getSender() {
        return $this->mailer->Sender;
    }

    public function setFrom($address, $name = '') {
        $this->mailer->From = $address;
        $this->mailer->FromName = $name;
    }

    public function getFrom() {
        return $this->mailer->From;
    }

    public function addAddress($address, $name = '') {
        $this->mailer->AddAddress($address, $name);
    }

    public function addCc($address, $name = '') {
        $this->mailer->AddCc($address, $name);
    }

    public function addBcc($address, $name = '') {
        $this->mailer->AddBcc($address, $name);
    }

    public function addReplyTo($address, $name = '') {
        $this->mailer->AddReplyTo($address, $name);
    }

    public function clearAddresses() {
        $this->mailer->ClearAdresses();
    }

    public function clearCcs() {
        $this->mailer->ClearCcs();
    }

    public function clearBccs() {
        $this->mailer->ClearBccs();
    }

    public function clearReplyTos() {
        $this->mailer->ClearReplyTos();
    }

    public function clearAllRecipients() {
        $this->mailer->ClearAllRecipients();
    }

    public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
        $this->mailer->AddAttachment($path, $name, $encoding, $type);
    }

    public function addStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream') {
        $this->mailer->AddStringAttachment($string, $filename, $encoding, $type);
    }

    public function addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
        $this->mailer->AddAmbeddedImage($path, $cid, $name, $encoding, $type);
    }

    public function clearAttachments() {
        $this->mailer->ClearAttachments();
    }

    function addCustomHeader($name, $value) {
        $this->mailer->AddCustomHeader("$name: $value");
    }

    function clearCustomHeaders() {
        $this->mailer->ClearCustomHeaders();
    }

    public function send() {
        if (!$this->mailer->Send())
			throw new Exception($this->mailer->ErrorInfo);
    }
}

?>
