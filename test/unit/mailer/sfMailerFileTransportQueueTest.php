<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once dirname(__FILE__) . '/../../bootstrap/unit.php';
require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/classes/Swift/Mailer.php';
spl_autoload_register(array('sfMailer', 'autoload'));
require_once dirname(__FILE__).'/fixtures/TestMailerTransport.class.php';

$t = new lime_test(4);

$dispatcher = new sfEventDispatcher();

// __construct()
$t->diag('__construct()');

try
{
  new sfMailerFileTransportQueue();

  $t->fail('__construct() throws an InvalidArgumentException when no path option is given');
}
catch (InvalidArgumentException $e)
{
  $t->pass('__construct() throws an InvalidArgumentException when no path option is given');
}

$path = sys_get_temp_dir().'/sf_mailer'.uniqid();
$queue = new sfMailerFileTransportQueue(array('path' => $path, 'time' => 2));
$message = sfMailerMessage::newInstance()
  ->setFrom('from@example.com')
  ->setTo('to@example.com')
  ->setSubject('Subject')
  ->setBody('Body')
;

// ->store()
$t->diag('->store()');
$queue->store($message);
$queue->store($message);
$queue->store($message);
$queue->store($message);
$count = 0;
foreach (new DirectoryIterator($path) as $file)
{
  if (!strpos($file, '.message'))
  {
    continue;
  }

  ++$count;
}
$t->is($count, 4, '->store() stores the messages under the "path" directory');

// ->send()
$t->diag('->send()');
$transport = new TestMailerTransport(2);
$queue->doSend($transport, $failed, array('time' => 1));
$t->is($transport->getSentCount(), 1, '->doSend() sends the messages in the queue');
$transport = new TestMailerTransport();
$queue = new sfMailerFileTransportQueue(array('path' => $path));
$queue->doSend($transport);
$t->is($transport->getSentCount(), 3, '->doSend() sends the messages in the queue');
