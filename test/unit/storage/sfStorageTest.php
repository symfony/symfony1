<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(11, new lime_output_color());

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

// ->initialize()
$t->diag('->initialize()');
$storage = new myStorage(array('foo' => 'bar'));
$t->is($storage->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

$storage = new myStorage();

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($storage, 'parameter');
