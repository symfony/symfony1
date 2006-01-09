<?php

class sfConfigTest extends UnitTestCase
{
  public function test_getset()
  {
    sfConfig::clear();

    sfConfig::set('foo', 'bar');
    $this->assertEqual('bar', sfConfig::get('foo'));

    $this->assertEqual('default_value', sfConfig::get('foo1', 'default_value'));
  }

  public function test_add()
  {
    sfConfig::clear();

    sfConfig::set('foo', 'bar');
    sfConfig::set('foo1', 'foo1');
    sfConfig::add(array('foo' => 'foo', 'bar' => 'bar'));

    $this->assertEqual('foo', sfConfig::get('foo'));
    $this->assertEqual('bar', sfConfig::get('bar'));
    $this->assertEqual('foo1', sfConfig::get('foo1'));

    sfConfig::clear();
    $this->assertEqual(null, sfConfig::get('foo1'));
  }
}

?>