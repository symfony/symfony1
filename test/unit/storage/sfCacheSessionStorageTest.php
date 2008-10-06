<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$_test_dir = realpath(dirname(__FILE__).'/../../');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));

$t = new lime_test(3, new lime_output_color());

// initialize the storage
try
{
  $storage = new sfCacheSessionStorage();
  $t->fail('sfCacheSessionStorage does not throw an exception when not provided a cache option.');
}
catch (InvalidArgumentException $e)
{
  $t->pass('sfCacheSessionStorage throws an exception when not provided a cache option.');
}


$storage = new sfCacheSessionStorage(array('cache' => array('class' => 'sfAPCCache', 'param' => array())));
$t->ok($storage instanceof sfStorage, 'sfCacheSessionStorage is an instance of sfStorage');

$storage->write('test', 123);

$t->is($storage->read('test'), 123, 'sfCacheSessionStorage can read data that has been written to storage.');

// shutdown the storage
// $storage->shutdown();
