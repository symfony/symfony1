<?php

class sfToolkitTest extends UnitTestCase
{
  public function test_stringToArray()
  {
    $tests = array(
      'foo=bar' => array('foo' => 'bar'),
      'foo1=bar1 foo=bar   ' => array('foo1' => 'bar1', 'foo' => 'bar'),
      'foo1="bar1 foo1"' => array('foo1' => 'bar1 foo1'),
      'foo1="bar1 foo1" foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      'foo1 = "bar1=foo1" foo=bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
      'foo1= \'bar1 foo1\'    foo  =     bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      'foo1=\'bar1=foo1\' foo = bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
      'foo1=  bar1 foo1 foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      'foo1="l\'autre" foo=bar' => array('foo1' => 'l\'autre', 'foo' => 'bar'),
      'foo1="l"autre" foo=bar' => array('foo1' => 'l"autre', 'foo' => 'bar'),
      'foo_1=bar_1' => array('foo_1' => 'bar_1'),
    );

    foreach ($tests as $string => $attributes)
    {
      $this->assertEqual($attributes, sfToolkit::stringToArray($string));
    }
  }
}
