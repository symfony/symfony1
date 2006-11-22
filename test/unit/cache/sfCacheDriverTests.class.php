<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfCacheDriverTests
{
  static public function launch($t, $cache)
  {
    $cache->setLifeTime(86400);
    $namespace = 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache';

    // ->set() ->get() ->has()
    $t->diag('->set() ->get() ->has()');
    $data = 'some random data to store in the cache system...';
    $t->ok($cache->set('test', $namespace, $data), '->set() returns true if data are stored in cache');
    $t->is($cache->get('test', $namespace), $data, '->get() retrieves data form the cache');
    $t->is($cache->has('test', $namespace), true, '->has() returns true if the cache exists');

    $t->is($cache->get('foo', $namespace), null, '->get() returns null if the cache does not exist');
    $t->is($cache->has('foo', $namespace), false, '->has() returns false if the cache does not exist');

    $data = 'another some random data to store in the cache system...';
    $t->ok($cache->set('test', $namespace, $data), '->set() returns true if data are stored in cache');
    $t->is($cache->get('test', $namespace), $data, '->set() retrieves data form the cache');

    // try to change some data in cache
    $cache->set('test', '', $data);
    $t->is($cache->get('test'), $data, '->set() takes a namespace as its second argument');

    // ->clean()
    $t->diag('->clean()');
    $data = 'some random data to store in the cache system...';
    $cache->set('test', $namespace, $data);
    $cache->set('test', '', $data);

    $cache->clean('', 'old');
    $t->is($cache->has('test', $namespace), true, '->clean() takes a namespace as its second argument');
    $t->is($cache->has('test'), true, '->clean() takes a namespace as its second argument');

    $cache->clean();
    $t->is($cache->has('test', $namespace), false, '->clean() removes all cache data from all namespaces');
    $t->is($cache->has('test'), false, '->clean() removes all cache data from all namespaces');

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
    $t->is($cache->has('test', $namespace), true, '->remove() takes a namespace as its first argument');
    $t->is($cache->has('test'), false, '->remove() takes a namespace as its first argument');

    $cache->set('test', '', $data);

    $cache->remove('test', $namespace);
    $t->is($cache->has('test', $namespace), false, '->remove() takes a namespace as its second argument');
    $t->is($cache->has('test'), true, '->remove() takes a namespace as its second argument');
  }
}
