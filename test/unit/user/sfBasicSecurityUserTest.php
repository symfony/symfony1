<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(22, new lime_output_color());

$context = new sfContext();

// ->initialize()
$t->diag('->initialize()');
$t->todo('->initialize() times out the user if no request made for a long time');
/*
sfConfig::set('sf_timeout', 0);
$user = new sfBasicSecurityUser();
$user->initialize($context);
$t->is($user->isTimedOut(), true, '->initialize() times out the user if no request made for a long time');
*/

$user = new sfBasicSecurityUser();
$user->initialize($context);

// ->listCredentials()
$t->diag('->listCredentials()');
$user->clearCredentials();
$user->addCredential('user');
$t->is($user->listCredentials(), array('user'), '->listCredentials() returns user credentials as an array');

// ->setAuthenticated() ->isAuthenticated()
$t->diag('->setAuthenticated() ->isAuthenticated()');
$t->is($user->isAuthenticated(), false, '->isAuthenticated() returns false by default');
$user->setAuthenticated(true);
$t->is($user->isAuthenticated(), true, '->isAuthenticated() returns true if the user is authenticated');
$user->setAuthenticated(false);
$t->is($user->isAuthenticated(), false, '->setAuthenticated() accepts a boolean as its first parameter');

// ->setTimedOut() ->getTimedOut()
sfConfig::set('sf_timeout', 86400);
$user = new sfBasicSecurityUser();
$user->initialize($context);
$t->diag('->setTimedOut() ->isTimedOut()');
$t->is($user->isTimedOut(), false, '->isTimedOut() returns false if the session is not timed out');
$user->setTimedOut();
$t->is($user->isTimedOut(), true, '->isTimedOut() returns true if the session is timed out');

// ->hasCredential()
$t->diag('->hasCredential()');
$user->clearCredentials();
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
