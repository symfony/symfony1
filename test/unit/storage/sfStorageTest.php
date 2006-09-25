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
require_once($_test_dir.'/unit/bootstrap.php');

$t = new lime_test(19, new lime_output_color());

class myStorage extends sfStorage
{
  function & read ($key) {}
  function & remove ($key) {}
  function shutdown () {}
  function write ($key, &$data) {}
}

class fakeStorage
{
}

$context = new sfContext();
$storage = new myStorage();
$storage->initialize($context);

// ::newInstance()
$t->diag('::newInstance()');
$t->isa_ok(sfStorage::newInstance('myStorage'), 'myStorage', '::newInstance() takes a storage class as its first parameter');
$t->isa_ok(sfStorage::newInstance('myStorage'), 'myStorage', '::newInstance() returns an instance of myStorage');

try
{
  sfStorage::newInstance('fakeStorage');
  $t->fail('::newInstance() throws a sfFactoryException if the class does not extends sfStorage');
}
catch (sfFactoryException $e)
{
  $t->pass('::newInstance() throws a sfFactoryException if the class does not extends sfStorage');
}

// ->initialize()
$t->diag('->initialize()');
$storage = sfStorage::newInstance('myStorage');
$t->is($storage->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$storage->initialize($context, array('foo' => 'bar'));
$t->is($storage->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

$storage = new myStorage();
$storage->initialize($context);

// ->getContext()
$t->diag('->getContext()');
$storage->initialize($context);
$t->is($storage->getContext(), $context, '->getContext() returns the current context');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($storage, 'parameter');
