<?php

require_once 'helper/TagHelper.php';

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

  public function test_cdata_section()
  {
    $this->assertEqual(cdata_section(''), '<![CDATA[]]>');
    $this->assertEqual(cdata_section('foobar'), '<![CDATA[foobar]]>');
  }

  public function test_escape_javascript()
  {
    $this->assertEqual(escape_javascript("alert('foo');\nalert(\"bar\");"), 'alert(\\\'foo\\\');\\nalert(\\"bar\\");');
  }
}
