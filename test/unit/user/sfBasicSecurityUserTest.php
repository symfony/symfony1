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
require_once($_test_dir.'/../lib/storage/sfStorage.class.php');
require_once($_test_dir.'/../lib/storage/sfSessionTestStorage.class.php');
require_once($_test_dir.'/../lib/user/sfUser.class.php');
require_once($_test_dir.'/../lib/user/sfSecurityUser.class.php');
require_once($_test_dir.'/../lib/user/sfBasicSecurityUser.class.php');

$t = new lime_test(15, new lime_output_color());

$context = new sfContext();
$user = new sfBasicSecurityUser();
$user->initialize($context);

// ->hasCredential()
$t->diag('->hasCredential()');
$t->is($user->hasCredential('admin'), false, '->hasCredential() returns false if user has not the credential');

$user->addCredential('admin');
$t->is($user->hasCredential('admin'), true, '->addCredential() takes a credential as its first argument');

// admin and user
$t->is($user->hasCredential(array('admin', 'user')), false, '->hasCredential() can takes an array of credential as a parameter');

// admin or user
$t->is($user->hasCredential(array(array('admin', 'user'))), true, '->hasCredential() can takes an array of credential as a parameter');

$user->addCredential('user');
$t->is($user->hasCredential('admin'), true);
$t->is($user->hasCredential('user'), true);

$user->addCredentials('superadmin', 'subscriber');
$t->is($user->hasCredential('subscriber'), true);
$t->is($user->hasCredential('superadmin'), true);

// admin and (user or subscriber)
$t->is($user->hasCredential(array(array('admin', array('user', 'subscriber')))), true);

$user->addCredentials(array('superadmin1', 'subscriber1'));
$t->is($user->hasCredential('subscriber1'), true);
$t->is($user->hasCredential('superadmin1'), true);

// admin and (user or subscriber) and (superadmin1 or subscriber1)
$t->is($user->hasCredential(array(array('admin', array('user', 'subscriber'), array('superadmin1', 'subscriber1')))), true);

// ->removeCredential()
$t->diag('->removeCredential()');
$user->removeCredential('user');
$t->is($user->hasCredential('user'), false);

// ->clearCredentials()
$t->diag('->clearCredentials()');
$user->clearCredentials();
$t->is($user->hasCredential('subscriber'), false);
$t->is($user->hasCredential('superadmin'), false);
