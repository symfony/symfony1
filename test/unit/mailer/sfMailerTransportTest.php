<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once dirname(__FILE__) . '/../../bootstrap/unit.php';
require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/classes/Swift.php';
Swift::registerAutoload();
sfMailer::initialize();
require_once dirname(__FILE__).'/fixtures/TestMailMessage.class.php';
require_once dirname(__FILE__).'/fixtures/TestMailerTransport.class.php';
require_once dirname(__FILE__).'/fixtures/TestMailerTransportQueue.class.php';

$t = new lime_test(38);

$dispatcher = new sfEventDispatcher();

// __construct()
$t->diag('__construct()');

try
{
  new sfMailerTransport('foo');

  $t->fail('__construct() throws an InvalidArgumentException exception if the strategy is not valid');
}
catch (InvalidArgumentException $e)
{
  $t->pass('__construct() throws an InvalidArgumentException exception if the strategy is not valid');
}

$transport = new sfMailerTransport('none');
$t->is($transport->getDeliveryStrategy(), 'none', '__construct() takes a delivery strategy as its first argument');

$transport = new sfMailerTransport('none', $transport1 = new TestMailerTransport());
$t->is($transport->getTransport(), $transport1, '__construct() takes a transport as its second argument');

$transport = new sfMailerTransport('none', null, $transport1 = new TestMailerTransportQueue());
$t->is($transport->getTransportQueue(), $transport1, '__construct() takes a transport queue as its third argument');

// ::validateDeliveryStrategy()
$t->diag('::validateDeliveryStrategy()');
try
{
  sfMailerTransport::validateDeliveryStrategy('foo');

  $t->fail('::validateDeliveryStrategy() throws an InvalidArgumentException exception if the strategy is not valid');
}
catch (InvalidArgumentException $e)
{
  $t->pass('::validateDeliveryStrategy() throws an InvalidArgumentException exception if the strategy is not valid');
}
$t->is(sfMailerTransport::validateDeliveryStrategy('queue'), sfMailerTransport::QUEUE, '::validateDeliveryStrategy() returns the strategy constant value');

// ->getDeliveryAddress() ->setDeliveryAddress()
$t->diag('->getDeliveryAddress() ->setDeliveryAddress()');
$transport = new sfMailerTransport('none');
$transport->setDeliveryAddress('foo@example.com');
$t->is($transport->getDeliveryAddress(), 'foo@example.com', '->setDeliveryAddress() sets the delivery address for the single_address strategy');

// ->getLogger() ->setLogger()
$t->diag('->getLogger() ->setLogger()');
$transport = new sfMailerTransport('none');
$transport->setLogger($logger = new sfMailerMessageLoggerPlugin($dispatcher));
$t->ok($transport->getLogger() === $logger, '->setLogger() sets the mailer logger');

// ->registerPlugin()
$t->diag('->registerPlugin()');
$logger = new sfMailerMessageLoggerPlugin($dispatcher);

class Test1MailerTransport extends sfMailerTransport
{
  public function getEventDispatcher()
  {
    return $this->eventDispatcher;
  }
}

$transport = new Test1MailerTransport('none');
$eventDispatcher = $transport->getEventDispatcher();

$eventDispatcher->dispatchEvent($eventDispatcher->createSendEvent($transport, Swift_Message::newInstance()), 'sendPerformed');
$t->is($logger->countMessages(), 0, '->registerPlugin() registers a plugin');

$transport->registerPlugin($logger);
$eventDispatcher->dispatchEvent($eventDispatcher->createSendEvent($transport, Swift_Message::newInstance()), 'sendPerformed');
$t->is($logger->countMessages(), 1, '->registerPlugin() registers a plugin');

$transport = new Test1MailerTransport('none', new TestMailerTransport());
$eventDispatcher = $transport->getEventDispatcher();

$transport->registerPlugin($logger);
$eventDispatcher->dispatchEvent($eventDispatcher->createSendEvent($transport, Swift_Message::newInstance()), 'sendPerformed');
$t->is($logger->countMessages(), 2, '->registerPlugin() registers the plugin for the normal transport');

$transport = new Test1MailerTransport('none', new TestMailerTransport(), new TestMailerTransportQueue());
$eventDispatcher = $transport->getEventDispatcher();

$transport->registerPlugin($logger);
$eventDispatcher->dispatchEvent($eventDispatcher->createSendEvent($transport, Swift_Message::newInstance()), 'sendPerformed');
$t->is($logger->countMessages(), 3, '->registerPlugin() registers the plugin for the queue transport');

// ::rerouteMessageTo()
$t->diag('::rerouteMessageTo()');
$message = Swift_Message::newInstance()
  ->setFrom('from@example.com')
  ->setTo('to@example.com')
  ->setCc('cc@example.com')
  ->setBcc('bcc@example.com')
;
sfMailerTransport::rerouteMessageTo($message, 'foo@example.com');
$t->is($message->getTo(), array('foo@example.com' => ''), '::rerouteMessageTo() replaces the to address with the given one');
$t->is($message->getCc(), array(), '::rerouteMessageTo() removes the cc addresses');
$t->is($message->getBcc(), array(), '::rerouteMessageTo() removes the bcc addresses');
$t->is($message->getHeaders()->get('X-Symfony-To')->getValue(), 'to@example.com', '::rerouteMessageTo() puts the to address in the X-Symfony-To header');
$t->is($message->getHeaders()->get('X-Symfony-Cc')->getValue(), 'cc@example.com', '::rerouteMessageTo() puts the cc address in the X-Symfony-CC header');
$t->is($message->getHeaders()->get('X-Symfony-Bcc')->getValue(), 'bcc@example.com', '::rerouteMessageTo() puts the bcc address in the X-Symfony-Bcc header');

// ->send()
$t->diag('->send()');
$transportNormal = new TestMailerTransport();
$transportQueue = new TestMailerTransportQueue();
$message = Swift_Message::newInstance()->setFrom('from@example.com')->setTo('to@example.com');

$t->diag('  "none" strategy');
$transport = new sfMailerTransport('none', $transportNormal, $transportQueue);
$transport->setLogger($logger = new sfMailerMessageLoggerPlugin($dispatcher));

$t->is($transport->send($message), false, '->send() returns false when the strategy is none');
$t->is($logger->countMessages(), 1, '->send() logs the message when the strategy is none');
$t->is($transportQueue->getQueuedCount(), 0, '->send() does not queue the message if strategy is none');
$t->is($transportNormal->getSentCount(), 0, '->send() does not send the message if strategy is none');
$transportNormal->reset();
$transportQueue->reset();

$t->diag('  "single_address" strategy');
$transport = new sfMailerTransport('single_address', $transportNormal, $transportQueue);
$transport->setDeliveryAddress('foo@example.com');

$t->is($transport->send($message), 1, '->send() returns the number of email sent');
$t->is(array_keys($message->getTo()), array($transport->getDeliveryAddress()), '->send() sends the message to the single address');
$t->is($transportQueue->getQueuedCount(), 0, '->send() does not queue the message if strategy is single_address');
$t->is($transportNormal->getSentCount(), 1, '->send() sends the message if strategy is single_address');
$transportNormal->reset();
$transportQueue->reset();

$t->diag('  "realtime" strategy');
$transport = new sfMailerTransport('realtime', $transportNormal, $transportQueue);

$t->is($transport->send($message), 1, '->send() returns the number of email sent');
$t->is($transportQueue->getQueuedCount(), 0, '->send() does not queue the message if strategy is realtime');
$t->is($transportNormal->getSentCount(), 1, '->send() sends the message if strategy is realtime');
$transportNormal->reset();
$transportQueue->reset();

$t->diag('  "queue" strategy');
$transport = new sfMailerTransport('queue', $transportNormal, $transportQueue);

$t->is($transport->send($message), 0, '->send() always returns 0 if strategy is queue');
$t->is($transportQueue->getQueuedCount(), 1, '->send() queues the message if strategy is queue');
$t->is($transportNormal->getSentCount(), 0, '->send() does not send the message if strategy is queue');
$transportNormal->reset();
$transportQueue->reset();

// ->sendNextImmediately()
$t->diag('->sendNextImmediately()');
$transport = new sfMailerTransport('queue', $transportNormal, $transportQueue);
$transport->sendNextImmediately();

$t->is($transport->send($message), 1, '->send() returns 1 if strategy is queue but the message has been forced for immediate delivery');
$t->is($transportQueue->getQueuedCount(), 0, '->send() sends the message if strategy is queue and message delivery has been forced');
$t->is($transportNormal->getSentCount(), 1, '->send() sends the message if strategy is queue and message delivery has been forced');
$transportNormal->reset();
$transportQueue->reset();

$t->is($transport->send($message), 0, '->send() forces immediate delivery only for one message');
$t->is($transportQueue->getQueuedCount(), 1, '->send() forces immediate delivery only for one message');
$t->is($transportNormal->getSentCount(), 0, '->send() forces immediate delivery only for one message');
