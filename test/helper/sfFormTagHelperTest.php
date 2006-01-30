<?php

require_once 'helper/TagHelper.php';
require_once 'helper/FormHelper.php';

Mock::generate('sfContext');

class sfFormTagHelperTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_form_checkbox_tag()
  {
    $actual = checkbox_tag('admin');
    $expected = '<input type="checkbox" name="admin" id="admin" value="1" />';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_input_hidden_tag()
  {
    $actual = input_hidden_tag('id', 3);
    $expected = '<input type="hidden" name="id" id="id" value="3" />';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_input_password_tag()
  {
    $actual = input_password_tag();
    $expected = '<input type="password" name="password" id="password" value="" />';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_radiobutton_tag()
  {
    $actual = radiobutton_tag("people", "david");
    $expected = '<input type="radio" name="people" value="david" />';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_select_tag()
  {
    $actual = select_tag("people", "<option>david</option>");
    $expected = '<select name="people" id="people"><option>david</option></select>';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_textarea_tag_size()
  {
    $actual = textarea_tag("body", "hello world", array("size" => "20x40"));
    $expected = '<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>';
    $this->assertEqual($expected, $actual);

    $actual = textarea_tag("body", "hello world", "size=20x40");
    $expected = '<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>';
    $this->assertEqual($expected, $actual);
  }

  public function test_form_input_tag()
  {
    $actual = input_tag("title", "Hello!");
    $expected = '<input type="text" name="title" id="title" value="Hello!" />';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_input_tag_class_string()
  {
    $actual = input_tag('title', 'Hello!', array('class' => 'admin'));
    $expected = '<input type="text" name="title" id="title" value="Hello!" class="admin" />';
    $this->assertEqual($expected, $actual);

    $actual = input_tag('title', 'Hello!', 'class=admin');
    $expected = '<input type="text" name="title" id="title" value="Hello!" class="admin" />';
    $this->assertEqual($expected, $actual);
  }

  public function test_object_for_select()
  {
    require_once('TestObject.php');

    $obj1 = new TestObject();
    $obj2 = new TestObject();
    $obj2->setText('text2');
    $obj2->setValue('value2');

    $actual = objects_for_select(array($obj1, $obj2), 'getValue', 'getText');
    $expected = "<option value=\"value\">text</option>\n<option value=\"value2\">text2</option>\n";
    $this->assertEqual($expected, $actual);

    $actual = objects_for_select(array($obj1, $obj2), 'getValue');
    $expected = "<option value=\"value\">value</option>\n<option value=\"value2\">value2</option>\n";
    $this->assertEqual($expected, $actual);

    try
    {
      $actual = objects_for_select(array($obj1, $obj2), 'getNonExistantMethod');
      $this->assertEqual($expected, $actual);

      $this->assertTrue(0);
    }
    catch (sfViewException $e)
    {
      $this->assertTrue(1);
    }

    try
    {
      $actual = objects_for_select(array($obj1, $obj2), 'getValue', 'getNonExistantMethod');
      $this->assertEqual($expected, $actual);

      $this->assertTrue(0);
    }
    catch (sfViewException $e)
    {
      $this->assertTrue(1);
    }
  }

/*
  public function test_form_tag()
  {
    $actual = tag();
    $expected = '<form action="http://www.example.com" method="post">';

    $this->assertEqual($expected, $actual);
  }

  public function test_form_tag_multipart()
  {
    $actual = form_tag(null, array('multipart' => true ));
    $expected = '<form action="http://www.example.com" enctype="multipart/form-data" method="post">';

    $this->assert_equal($expected, $actual);
  }
*/

  public function test_boolean_optios()
  {
    $actual = checkbox_tag('admin', 1, true, array('disabled' => true, 'readonly' => 'yes'));
    $expected = '<input type="checkbox" name="admin" id="admin" value="1" disabled="disabled" readonly="readonly" checked="checked" />';
    $this->assertEqual($actual, $expected);

    $actual = checkbox_tag('admin', 1, true, array('disabled' => false, 'readonly' => null));
    $expected = '<input type="checkbox" name="admin" id="admin" value="1" checked="checked" />';
    $this->assertEqual($actual, $expected);

    $actual = select_tag('people', "<option>david</option>", array('multiple' => true));
    $expected = '<select name="people" id="people" multiple="multiple"><option>david</option></select>';
    $this->assertEqual($actual, $expected);

    $actual = select_tag('people', "<option>david</option>", array('multiple' => null));
    $expected = '<select name="people" id="people"><option>david</option></select>';
    $this->assertEqual($actual, $expected);
  }

  public function test_stringify_symbol_keys()
  {
    $actual = input_tag('title', 'Hello!', array('id'=> 'admin'));
    $expected = '<input type="text" name="title" id="admin" value="Hello!" />';
    $this->assertEqual($expected, $actual);
  }

}

?>