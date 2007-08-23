<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(17, new lime_output_color());

class myStorage extends sfStorage
{
  public function read($key) {}
  public function remove($key) {}
  public function shutdown() {}
  public function write($key, $data) {}
}

class fakeStorage
{
}

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
$storage->initialize(array('foo' => 'bar'));
$t->is($storage->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

$storage = new myStorage();
$storage->initialize();

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($storage, 'parameter');
