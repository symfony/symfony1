<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/util/sfParameterHolder.class.php');
require_once($_test_dir.'/../lib/util/sfMixer.class.php');
require_once($_test_dir.'/../lib/response/sfResponse.class.php');

class sfException extends Exception {}
class sfFactoryException extends sfException {}

class myResponse extends sfResponse
{
  function shutdown() {}
}

class fakeResponse
{
}

$t = new lime_test(24, new lime_output_color());

$context = new sfContext();

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
$t->is($response->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$response->initialize($context, array('foo' => 'bar'));
$t->is($response->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

// ->getContext()
$t->diag('->getContext()');
$response->initialize($context);
$t->is($response->getContext(), $context, '->getContext() returns the current context');

// ->setContext()
$t->diag('->setContext()');
$response->setContext(null);
$t->is($response->getContext(), null, '->setContext() changes the current context');
$response->setContext($context);

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

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($response, 'parameter');

// mixins
require_once($_test_dir.'/unit/sfMixerTest.class.php');
$mixert = new sfMixerTest($t);
$mixert->launchTests($response, 'sfResponse');
