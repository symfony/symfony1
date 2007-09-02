<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class myResponse extends sfResponse
{
  function serialize() {}
  function unserialize($serialized) {}
}

class fakeResponse
{
}

$t = new lime_test(23, new lime_output_color());

$dispatcher = new sfEventDispatcher();

// ::newInstance()
$t->diag('::newInstance()');
$t->isa_ok(sfResponse::newInstance('myResponse'), 'myResponse', '::newInstance() takes a response class as its first parameter');
$t->isa_ok(sfResponse::newInstance('myResponse'), 'myResponse', '::newInstance() returns an instance of myResponse');

try
{
  sfResponse::newInstance('fakeResponse');
  $t->fail('::newInstance() throws a sfFactoryException if the class does not extends sfResponse');
}
catch (sfFactoryException $e)
{
  $t->pass('::newInstance() throws a sfFactoryException if the class does not extends sfResponse');
}

// ->initialize()
$t->diag('->initialize()');
$response = sfResponse::newInstance('myResponse');
$response->initialize($dispatcher, array('foo' => 'bar'));
$t->is($response->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

// ->getContent() ->setContent()
$t->diag('->getContent() ->setContent()');
$t->is($response->getContent(), null, '->getContent() returns the current response content which is null by default');
$response->setContent('test');
$t->is($response->getContent(), 'test', '->setContent() sets the response content');

// ->sendContent()
$t->diag('->sendContent()');
ob_start();
$response->sendContent();
$content = ob_get_clean();
$t->is($content, 'test', '->sendContent() output the current response content');

// ->serialize() ->unserialize()
$t->diag('->serialize() ->unserialize()');
$t->ok(sfResponse::newInstance('myResponse') instanceof Serializable, 'sfResponse implements the Serializable interface');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$response = sfResponse::newInstance('myResponse');
$response->initialize($dispatcher);
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($response, 'parameter');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($dispatcher, $response, 'response');
