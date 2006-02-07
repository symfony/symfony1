<?php
require_once 'helper/ObjectHelper.php';
require_once 'TestObject.php';

class sfObjectHelperTest extends UnitTestCase
{
  public function test_object_textarea_tag()
  {
    $obj1 = new TestObject();

    $this->assertEqual(object_textarea_tag($obj1, 'getValue'),
                       '<textarea name="value" id="value">value</textarea>');
    $this->assertEqual(object_textarea_tag($obj1, 'getValue', 'size=60x10'),
                       '<textarea name="value" id="value" rows="10" cols="60">value</textarea>');
  }

  public function test_objects_for_select()
  {
    $obj1 = new TestObject();
    $obj2 = new TestObject();
    $obj2->setText('text2');
    $obj2->setValue('value2');

    $actual = objects_for_select(Array($obj1, $obj2), 'getValue', 'getText', 'value');
    $expected = "<option value=\"value\" selected=\"selected\">text</option>\n<option value=\"value2\">text2</option>\n";
    $this->assertEqual($expected, $actual);

    $actual = objects_for_select(Array($obj1, $obj2), 'getValue');
    $expected = "<option value=\"value\">value</option>\n<option value=\"value2\">value2</option>\n";
    $this->assertEqual($expected, $actual);

    try
    {
      $actual = objects_for_select(Array($obj1, $obj2), 'getNonExistantMethod');
      $this->assertEqual($expected, $actual);

      $this->assertTrue(0);
    }
    catch (sfViewException $e)
    {
      $this->assertTrue(1);
    }

    try
    {
      $actual = objects_for_select(Array($obj1, $obj2), 'getValue', 'getNonExistantMethod');
      $this->assertEqual($expected, $actual);

      $this->assertTrue(0);
    }
    catch (sfViewException $e)
    {
      $this->assertTrue(1);
    }
  }

  public function test_object_input_hidden_tag()
  {
    $obj1 = new TestObject();

    $this->assertEqual(object_input_hidden_tag($obj1, 'getValue'),
                       '<input type="hidden" name="value" id="value" value="value" />');
  }

  public function test_object_input_tag()
  {
    $obj1 = new TestObject();

    $this->assertEqual(object_input_tag($obj1, 'getValue'),
                       '<input type="text" name="value" id="value" value="value" />');
  }

  public function test_object_checkbox_tag()
  {
    $obj1 = new TestObject();

    $this->assertEqual(object_checkbox_tag($obj1, 'getValue'),
                       '<input type="checkbox" name="value" id="value" value="1" />');
  }
}
