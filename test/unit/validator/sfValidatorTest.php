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
require_once($_test_dir.'/../lib/validator/sfValidator.class.php');

$t = new lime_test(14, new lime_output_color());

class myValidator extends sfValidator
{
  function execute (&$value, &$error) {}
}

$context = new sfContext();
$validator = new myValidator();
$validator->initialize($context);

// ->getContext()
$t->diag('->getContext()');
$validator->initialize($context);
$t->is($validator->getContext(), $context, '->getContext() returns the current context');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($validator, 'parameter');
