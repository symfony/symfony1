<?php

require_once 'symfony/cache/sfCache.class.php';
require_once 'symfony/cache/sfFileCache.class.php';
require_once 'symfony/exception/sfException.class.php';
require_once 'symfony/exception/sfCacheException.class.php';
require_once 'symfony/util/sfToolkit.class.php';

class sfFileCacheTest extends UnitTestCase
{
  private $cache = null;

  public function setUp()
  {
    @define(SF_LOGGING_ACTIVE, 0);
    @mkdir('/tmp/cachedir');
    $this->cache = new sfFileCache('/tmp/cachedir');
  }

  public function tearDown()
  {
    sfToolkit::clearDirectory('/tmp/cachedir');
  }

  public function test_set()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony/test/sfCache', $data);
    $this->assertEqual($data, $this->cache->get('test', 'symfony/test/sfCache'));

    $this->cache->set('test', '', $data);
    $this->assertEqual($data, $this->cache->get('test'));
  }

  public function test_clear()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony/test/sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->clean('', 'old');
    $this->assertTrue($this->cache->has('test', 'symfony/test/sfCache'));
    $this->assertTrue($this->cache->has('test'));

    $this->cache->clean();
    $this->assertFalse($this->cache->has('test', 'symfony/test/sfCache'));
    $this->assertFalse($this->cache->has('test'));

    $this->cache->set('test', 'symfony/test/sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->clean('symfony/test/sfCache');
    $this->assertFalse($this->cache->has('test', 'symfony/test/sfCache'));
    $this->assertTrue($this->cache->has('test'));
  }

  public function test_remove()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony/test/sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->remove('test');
    $this->assertTrue($this->cache->has('test', 'symfony/test/sfCache'));
    $this->assertFalse($this->cache->has('test'));

    $this->cache->set('test', '', $data);

    $this->cache->remove('test', 'symfony/test/sfCache');
    $this->assertFalse($this->cache->has('test', 'symfony/test/sfCache'));
    $this->assertTrue($this->cache->has('test'));
  }
}

?>