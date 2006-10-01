<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Form'));

$t = new lime_test(16, new lime_output_color());

$context = new sfContext();

// checkbox_tag()
$t->diag('checkbox_tag()');
$actual = checkbox_tag('admin');
$expected = '<input type="checkbox" name="admin" id="admin" value="1" />';
$t->is($actual, $expected);

// input_hidden_tag()
$t->diag('input_hidden_tag()');
$actual = input_hidden_tag('id', 3);
$expected = '<input type="hidden" name="id" id="id" value="3" />';
$t->is($actual, $expected);

// input_password_tag()
$t->diag('input_password_tag()');
$actual = input_password_tag();
$expected = '<input type="password" name="password" id="password" value="" />';
$t->is($actual, $expected);

// radiobutton_tag()
$t->diag('radiobutton_tag()');
$actual = radiobutton_tag("people", "fabien");
$expected = '<input type="radio" name="people" id="people" value="fabien" />';
$t->is($actual, $expected);

// options_for_select()
$t->diag('options_for_select()');
$t->is("<option value=\"0\" selected=\"selected\">item1</option>\n<option value=\"1\">item2</option>\n",
                   options_for_select(array('item1', 'item2'), '0'));

// select_tag()
$t->diag('select_tag()');
$actual = select_tag("people", "<option>fabien</option>");
$expected = '<select name="people" id="people"><option>fabien</option></select>';
$t->is($actual, $expected);

// textarea_tag_size()
$t->diag('textarea_tag_size()');
$actual = textarea_tag("body", "hello world", array("size" => "20x40"));
$expected = '<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>';
$t->is($actual, $expected);

$actual = textarea_tag("body", "hello world", "size=20x40");
$expected = '<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>';
$t->is($actual, $expected);

// input_tag()
$t->diag('input_tag()');
$actual = input_tag("title", "Hello!");
$expected = '<input type="text" name="title" id="title" value="Hello!" />';
$t->is($actual, $expected);

$actual = input_tag('title', 'Hello!', array('class' => 'admin'));
$expected = '<input type="text" name="title" id="title" value="Hello!" class="admin" />';
$t->is($actual, $expected);

$actual = input_tag('title', 'Hello!', 'class=admin');
$expected = '<input type="text" name="title" id="title" value="Hello!" class="admin" />';
$t->is($actual, $expected);

/*
// tag()
    $actual = tag();
    $expected = '<form action="http://www.example.com" method="post">';

    $t->is($actual, $expected);

// tag_multipart()
    $actual = form_tag(null, array('multipart' => true ));
    $expected = '<form action="http://www.example.com" enctype="multipart/form-data" method="post">';

    $this->assert_equal($actual, $expected);
*/

// checkbox_tag()
$t->diag('checkbox_tag()');
$actual = checkbox_tag('admin', 1, true, array('disabled' => true, 'readonly' => 'yes'));
$expected = '<input type="checkbox" name="admin" id="admin" value="1" disabled="disabled" readonly="readonly" checked="checked" />';
$t->is($actual, $expected);

$actual = checkbox_tag('admin', 1, true, array('disabled' => false, 'readonly' => null));
$expected = '<input type="checkbox" name="admin" id="admin" value="1" checked="checked" />';
$t->is($actual, $expected);

// select_tag()
$t->diag('select_tag()');
$actual = select_tag('people', "<option>fabien</option>", array('multiple' => true));
$expected = '<select name="people[]" id="people" multiple="multiple"><option>fabien</option></select>';
$t->is($actual, $expected);

$actual = select_tag('people', "<option>fabien</option>", array('multiple' => null));
$expected = '<select name="people" id="people"><option>fabien</option></select>';
$t->is($actual, $expected);

// input_tag()
$t->diag('input_tag()');
$actual = input_tag('title', 'Hello!', array('id'=> 'admin'));
$expected = '<input type="text" name="title" id="admin" value="Hello!" />';
$t->is($actual, $expected);
