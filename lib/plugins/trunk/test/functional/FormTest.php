<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$t = new lime_test(6, new lime_output_color());

// test for ticket #4935
$user = new User();
$profile = $user->getProfile();

$userForm = new UserForm($user);
$profileForm = new ProfileForm($profile);
unset($profileForm['id'], $profileForm['user_id']);

$userForm->embedForm('Profile', $profileForm);

$data = array('username' => 'jwage',
              'password' => 'changeme',
              'Profile'  => array(
                  'first_name' => 'Jonathan',
                  'last_name'  => 'Wage'
                ));

$userForm->bind($data);
$userForm->save();

$t->is($user->getId() > 0, true);
$t->is($user->getId(), $profile->getUserId());
$t->is($user->getUsername(), 'jwage');
$t->is($profile->getFirstName(), 'Jonathan');

$userCount = Doctrine_Query::create()
  ->from('User u')
  ->count();

$t->is($userCount, 1);

$profileCount = Doctrine_Query::create()
  ->from('Profile p')
  ->count();

$t->is($profileCount, 1);