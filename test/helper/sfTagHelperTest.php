<?php

require_once 'symfony/helper/TagHelper.php';
require_once 'symfony/core/sfContext.class.php';

Mock::generate('sfContext');

class sfTagHelperTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_tag()
  {
    $this->assertEqual(tag(''), '');
    $this->assertEqual(tag('br'), '<br />');
    $this->assertEqual(tag('p', null, true), '<p>');
    $this->assertEqual(tag('br', array('class' => 'foo'), false), '<br class="foo" />');
    $this->assertEqual(tag('br', 'class=foo', false), '<br class="foo" />');
    $this->assertEqual(tag('p', array('class' => 'foo', 'id' => 'bar'), true), '<p class="foo" id="bar">');
    //$this->assertEqual(tag('br', array('class' => '"foo"')), '<br class="&quot;foo&quot;" />');
  }

  public function test_content_tag()
  {
    $this->assertEqual(content_tag(''), '');
    $this->assertEqual(content_tag('', ''), '');
    $this->assertEqual(content_tag('p', 'Toto'), '<p>Toto</p>');
    $this->assertEqual(content_tag('p', ''), '<p></p>');
  }

  public function test_parse_attributes()
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
      $this->assertEqual($attributes, _parse_attributes($string));
    }
  }
}

?>