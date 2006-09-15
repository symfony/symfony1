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
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/util/sfToolkit.class.php');
require_once($_test_dir.'/../lib/cache/sfCache.class.php');
require_once($_test_dir.'/../lib/cache/sfFileCache.class.php');

$t = new lime_test(12, new lime_output_color());

// setup
sfConfig::set('sf_logging_active', false);
$temp = tempnam('/tmp/cachedir', 'tmp');
unlink($temp);
@mkdir($temp);
$dir = $temp;
$cache = new sfFileCache($temp);
$namespace = 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache';

// ->set()
$t->diag('->set()');
$data = 'some random data to store in the cache system...';
$cache->set('test', $namespace, $data);
$t->is($cache->get('test', $namespace), $data, '->set() stores data in a file');

$cache->set('test', '', $data);
$t->is($cache->get('test'), $data, '->set() takes a namespace as its second argument');

// ->clear()
$t->diag('->clear()');
$data = 'some random data to store in the cache system...';
$cache->set('test', $namespace, $data);
$cache->set('test', '', $data);

$cache->clean('', 'old');
$t->is($cache->has('test', $namespace), true, '->clean() takes a file name as its second argument');
$t->is($cache->has('test'), true, '->clean() takes a file name as its second argument');

$cache->clean();
$t->is($cache->has('test', $namespace), false, '->clean() removes all cache files from all namespaces');
$t->is($cache->has('test'), false, '->clean() removes all cache files from all namespaces');

$cache->set('test', $namespace, $data);
$cache->set('test', '', $data);

$cache->clean($namespace);
$t->is($cache->has('test', $namespace), false, '->clean() takes a namespace as its first argument');
$t->is($cache->has('test'), true, '->clean() takes a namespace as its first argument');

// ->remove()
$t->diag('->remove()');
$data = 'some random data to store in the cache system...';
$cache->set('test', $namespace, $data);
$cache->set('test', '', $data);

$cache->remove('test');
$t->is($cache->has('test', $namespace), true, '->remove() takes a file as its first argument');
$t->is($cache->has('test'), false, '->remove() takes a file as its first argument');

$cache->set('test', '', $data);

$cache->remove('test', $namespace);
$t->is($cache->has('test', $namespace), false, '->remove() takes a namespace as its second argument');
$t->is($cache->has('test'), true, '->remove() takes a namespace as its second argument');

// teardown
sfToolkit::clearDirectory($dir);
rmdir($dir);
