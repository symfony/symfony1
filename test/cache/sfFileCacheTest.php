<?php

class sfFileCacheTest extends UnitTestCase
{
  private $cache = null;
  private $dir;

  public function setUp()
  {
    sfConfig::set('sf_logging_active', 0);
    $temp = tempnam('/tmp/cachedir', 'tmp');
    unlink($temp);
    @mkdir($temp);
    $this->dir = $temp;
    $this->cache = new sfFileCache($temp);
  }

  public function tearDown()
  {
    sfToolkit::clearDirectory($this->dir);
    rmdir($this->dir);
  }

  public function test_set()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache', $data);
    $this->assertEqual($data, $this->cache->get('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));

    $this->cache->set('test', '', $data);
    $this->assertEqual($data, $this->cache->get('test'));
  }

  public function test_clear()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->clean('', 'old');
    $this->assertTrue($this->cache->has('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));
    $this->assertTrue($this->cache->has('test'));

    $this->cache->clean();
    $this->assertFalse($this->cache->has('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));
    $this->assertFalse($this->cache->has('test'));

    $this->cache->set('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->clean('symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache');
    $this->assertFalse($this->cache->has('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));
    $this->assertTrue($this->cache->has('test'));
  }

  public function test_remove()
  {
    $data = 'some random data to store in the cache system...';
    $this->cache->set('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache', $data);
    $this->cache->set('test', '', $data);

    $this->cache->remove('test');
    $this->assertTrue($this->cache->has('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));
    $this->assertFalse($this->cache->has('test'));

    $this->cache->set('test', '', $data);

    $this->cache->remove('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache');
    $this->assertFalse($this->cache->has('test', 'symfony'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'sfCache'));
    $this->assertTrue($this->cache->has('test'));
  }
}
