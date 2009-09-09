<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestMailerTransport implements Swift_Transport
{
  protected
    $foo = null,
    $started = false,
    $count = 0;

  public function isStarted() { return $this->started; }
  public function start() { $this->started = true; }
  public function stop() { $this->started = false; }
  public function registerPlugin(Swift_Events_EventListener $plugin) {}

  public function setFoo($foo)
  {
    $this->foo = $foo;
  }

  public function getFoo()
  {
    return $this->foo;
  }

  public function getSentCount()
  {
    return $this->count;
  }

  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    ++$this->count;

    return 1;
  }

  public function reset()
  {
    $this->count = 0;
    $this->started = false;
  }
}
