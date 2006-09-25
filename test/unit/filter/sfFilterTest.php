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

$t = new lime_test(23, new lime_output_color());

class myFilter extends sfFilter
{
  public function isFirstCallBeforeExecution()
  {
    return parent::isFirstCallBeforeExecution();
  }

  public function isFirstCallBeforeRendering()
  {
    return parent::isFirstCallBeforeRendering();
  }

  public function isFirstCall($type = 'beforeExecution')
  {
    return parent::isFirstCall($type);
  }
}

$context = new sfContext();
$filter = new myFilter();

// ->initialize()
$t->diag('->initialize()');
$filter = new myFilter();
$t->is($filter->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$filter->initialize($context, array('foo' => 'bar'));
$t->is($filter->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

// ->getContext()
$t->diag('->getContext()');
$filter->initialize($context);
$t->is($filter->getContext(), $context, '->getContext() returns the current context');

// ->isFirstCall()
$t->diag('->isFirstCall()');
$t->is($filter->isFirstCall('beforeExecution'), true, '->isFirstCall() returns true if this is the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');

// ->isFirstCallBeforeExecution()
$filter1 = new myFilter();
$filter2 = new myFilter();
$t->diag('->isFirstCallBeforeExecution()');
$t->is($filter1->isFirstCall('beforeExecution'), $filter2->isFirstCallBeforeExecution(), '->isFirstCallBeforeExecution() is an alias for ->isFirstCall() with "beforeExecution" as an argument');
$t->is($filter1->isFirstCall('beforeExecution'), $filter2->isFirstCallBeforeExecution(), '->isFirstCallBeforeExecution() is an alias for ->isFirstCall() with "beforeExecution" as an argument');

// ->isFirstCallBeforeRendering()
$filter1 = new myFilter();
$filter2 = new myFilter();
$t->diag('->isFirstCallBeforeRendering()');
$t->is($filter1->isFirstCall('beforeRendering'), $filter2->isFirstCallBeforeRendering(), '->isFirstCallBeforeRendering() is an alias for ->isFirstCall() with "beforeRendering" as an argument');
$t->is($filter1->isFirstCall('beforeRendering'), $filter2->isFirstCallBeforeRendering(), '->isFirstCallBeforeRendering() is an alias for ->isFirstCall() with "beforeRendering" as an argument');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($filter, 'parameter');
