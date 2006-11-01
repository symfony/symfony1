<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/../lib/util/sfFillInForm.class.php');

$t = new lime_test(42, new lime_output_color());

$html = <<<EOF
<html>
<body>
  <form name="form1" action="/go" method="POST">
    <input type="hidden" name="hidden_input" value="1" />
    <input type="text" name="empty_input_text" value="" />
    <input type="text" name="input_text" value="default_value" />
    <input type="checkbox" name="input_checkbox" value="1" checked="checked" />
    <input type="checkbox" name="input_checkbox_not_checked" value="1" />
    <input type="password" name="password" value="" />
    <textarea name="textarea">content</textarea>
    <select name="select">
      <option value="first">first</option>
      <option value="selected" selected="selected">selected</option>
      <option value="last">last</option>
    </select>
    <select name="select_multiple" multiple="multiple">
      <option value="first">first</option>
      <option value="selected" selected="selected">selected</option>
      <option value="last" selected="selected">last</option>
    </select>
    <input name="article[title]" value="title"/>
    <select name="article[category]" multiple="multiple">
      <option value="1">1</option>
      <option value="2" selected="selected">2</option>
      <option value="3" selected="selected">3</option>
    </select>
    <input name="article[or][much][longer]" value="very long!"/>
    <input type="submit" name="submit" value="submit" />
  </form>
</body>
</html>
EOF;

$dom = new DomDocument('1.0', 'UTF-8');
$dom->loadHTML($html);

//print $dom->saveXML();

// ->fillInDom()
$t->diag('->fillInDom()');
$f = new sfFillInForm();

// default values
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, array()));
$t->is(get_input_value($xml, 'hidden_input'), '1', '->fillInDom() preserves default values for hidden input');
$t->is(get_input_value($xml, 'input_text'), 'default_value', '->fillInDom() preserves default values for text input');
$t->is(get_input_value($xml, 'empty_input_text'), '', '->fillInDom() preserves default values for text input');
$t->is(get_input_value($xml, 'password'), '', '->fillInDom() preserves default values for password input');
$t->is(get_input_value($xml, 'input_checkbox', 'checked'), 'checked', '->fillInDom() preserves default values for checkbox');
$t->is(get_input_value($xml, 'input_checkbox_not_checked', 'checked'), '', '->fillInDom() preserves default values for checkbox');
$t->is($xml->xpath('//form[@name="form1"]/textarea'), array('content'), '->fillInDom() preserves default values for textarea');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select"]/option[@selected="selected"]'), array('selected'), '->fillInDom() preserves default values for select');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select_multiple"]/option[@selected="selected"]'), array('selected', 'last'), '->fillInDom() preserves default values for multiple select');
$t->is(get_input_value($xml, 'article[title]'), 'title', '->fillInDom() preserves default values for text input');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="article[category]"]/option[@selected="selected"]'), array(2, 3), '->fillInDom() preserves default values for select');

// test with article[title]
$values = array(
  'article' => array(
    'title'    => 'my article title',
    'category' => array(1, 2),
  ),
);
$f = new sfFillInForm();
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, $values));
$t->is(get_input_value($xml, 'article[title]'), 'my article title', '->fillInDom() fills in values for article[title] fields');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="article[category]"]/option[@selected="selected"]'), array(1, 2), '->fillInDom() fills in values for article[title] fields');

$values = array(
  'hidden_input' => 2,
  'empty_input_text' => 'input text',
  'input_text' => 'my input text',
  'input_checkbox' => false,
  'input_checkbox_not_checked' => true,
  'password' => 'mypassword',
  'select' => 'first',
  'select_multiple' => array('first', 'last'),
  'textarea' => 'my content',
  'article[title]' => 'my article title',
  'article[category]' => array(1, 2),
);

$f = new sfFillInForm();
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, $values));
$t->is(get_input_value($xml, 'hidden_input'), '2', '->fillInDom() fills in values for hidden input');
$t->is(get_input_value($xml, 'input_text'), 'my input text', '->fillInDom() fills in values for text input');
$t->is(get_input_value($xml, 'empty_input_text'), 'input text', '->fillInDom() fills in values for text input');
$t->is(get_input_value($xml, 'password'), 'mypassword', '->fillInDom() fills in values for password input');
$t->is(get_input_value($xml, 'input_checkbox', 'checked'), '', '->fillInDom() fills in values for checkbox');
$t->is(get_input_value($xml, 'input_checkbox_not_checked', 'checked'), 'checked', '->fillInDom() fills in values for checkbox');
$t->is($xml->xpath('//form[@name="form1"]/textarea'), array('my content'), '->fillInDom() fills in values for textarea');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select"]/option[@selected="selected"]'), array('first'), '->fillInDom() fills in values for select');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select_multiple"]/option[@selected="selected"]'), array('first', 'last'), '->fillInDom() fills in values for multiple select');
$t->is(get_input_value($xml, 'article[title]'), 'my article title', '->fillInDom() fills in values for text input');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="article[category]"]/option[@selected="selected"]'), array(1, 2), '->fillInDom() fills in values for select');

// ->setTypes()
$t->diag('->setTypes()');
$f = new sfFillInForm();
$f->setTypes(array('text', 'checkbox', 'radio'));
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, $values));
$t->is(get_input_value($xml, 'hidden_input'), '1', '->setTypes() allows to prevent some input fields from being filled');
$t->is(get_input_value($xml, 'password'), '', '->setTypes() allows to prevent some input fields from being filled');
$t->is(get_input_value($xml, 'input_text'), 'my input text', '->setTypes() allows to prevent some input fields from being filled');

// ->setSkipFields()
$t->diag('->setSkipFields()');
$f = new sfFillInForm();
$f->setSkipFields(array('input_text', 'input_checkbox', 'textarea', 'select_multiple', 'article[title]'));
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, $values));
$t->is(get_input_value($xml, 'hidden_input'), '2', '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'input_text'), 'default_value', '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'empty_input_text'), 'input text', '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'password'), 'mypassword', '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'input_checkbox', 'checked'), 'checked', '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'input_checkbox_not_checked', 'checked'), 'checked', '->setSkipFields() allows to prevent some fields to be filled');
$t->is($xml->xpath('//form[@name="form1"]/textarea'), array('content'), '->setSkipFields() allows to prevent some fields to be filled');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select"]/option[@selected="selected"]'), array('first'), '->setSkipFields() allows to prevent some fields to be filled');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="select_multiple"]/option[@selected="selected"]'), array('selected', 'last'), '->setSkipFields() allows to prevent some fields to be filled');
$t->is(get_input_value($xml, 'article[title]'), 'title', '->setSkipFields() allows to prevent some fields to be filled');
$t->is($xml->xpath('//form[@name="form1"]/select[@name="article[category]"]/option[@selected="selected"]'), array(1, 2), '->setSkipFields() allows to prevent some fields to be filled');

// ->addconverter()
$t->diag('->addConverter()');
$f = new sfFillInForm();
$f->addConverter('str_rot13', array('input_text', 'textarea'));
$xml = simplexml_import_dom($f->fillInDom(clone $dom, null, $values));
$t->is(get_input_value($xml, 'input_text'), str_rot13('my input text'), '->addConverter() register a callable to be called for each value');
$t->is(get_input_value($xml, 'empty_input_text'), 'input text', '->addConverter() register a callable to be called for each value');
$t->is(get_input_value($xml, 'input_checkbox', 'checked'), '', '->addConverter() register a callable to be called for each value');
$t->is($xml->xpath('//form[@name="form1"]/textarea'), array(str_rot13('my content')), '->addConverter() register a callable to be called for each value');

function get_input_value($xml, $name, $attribute = 'value', $form = null)
{
  $xpath = ($form ? '//form[@name="'.$form.'"]' : '//form').sprintf('/input[@name="%s"]', $name);

  $values = $xml->xpath($xpath);

  return (string) $values[0][$attribute];
}
