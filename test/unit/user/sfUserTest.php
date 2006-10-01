<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../bootstrap.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(27, new lime_output_color());

$context = new sfContext();
$user = new sfUser();
$user->initialize($context);

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'parameter');

// attribute holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'attribute');

// mixins
require_once($_test_dir.'/unit/sfMixerTest.class.php');
$mixert = new sfMixerTest($t);
$mixert->launchTests($user, 'sfUser');
