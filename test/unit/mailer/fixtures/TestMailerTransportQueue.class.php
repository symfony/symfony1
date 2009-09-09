<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestMailerTransportQueue extends sfMailerTransportQueue
{
  protected
    $model = null,
    $messages = array();

  public function setModel($model)
  {
    $this->model = $model;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function store(Swift_Mime_Message $message)
  {
    $this->messages[] = $message;

    return 0;
  }

  public function doSend(Swift_Transport $transport, &$failedRecipients = null, $max = 0)
  {
    foreach ($this->messages as $message)
    {
      $transport->send($message);
    }

    $this->messages = array();
  }

  public function getMessages()
  {
    return $this->messages;
  }

  public function getQueuedCount()
  {
    return count($this->messages);
  }

  public function reset()
  {
    $this->messages = array();
  }
}
