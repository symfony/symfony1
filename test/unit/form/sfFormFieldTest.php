<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(25, new lime_output_color());

$schema = new sfWidgetFormSchema();
$schema->setNameFormat('article[%s]');
$widget = new sfWidgetFormInput();
$schema['title'] = $widget;

$errorSchema = new sfValidatorErrorSchema(new sfValidatorString());
$errorSchema->addError($error = new sfValidatorError(new sfValidatorString(), 'error'), 'title');
$parent = new sfFormField($schema, null, 'article', array('title' => 'symfony'), $errorSchema);
$f = $parent['title'];

// ArrayAccess interface
$t->diag('ArrayAccess interface');
$t->is(isset($parent['title']), true, 'sfFormField implements the ArrayAccess interface');
$t->is(isset($parent['title1']), false, 'sfFormField implements the ArrayAccess interface');
$t->is($parent['title'], $f, 'sfFormField implements the ArrayAccess interface');
try
{
  unset($parent['title']);
  $t->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
}
catch (LogicException $e)
{
  $t->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
}

try
{
  $parent['title'] = null;
  $t->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
}
catch (LogicException $e)
{
  $t->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
}

try
{
  $f['title'];
  $t->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
}
catch (LogicException $e)
{
  $t->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
}

try
{
  $parent['title1'];
  $t->fail('sfFormField implements the ArrayAccess interface but in read-only mode');
}
catch (LogicException $e)
{
  $t->pass('sfFormField implements the ArrayAccess interface but in read-only mode');
}

// ->getValue() ->getWidget() ->getParent() ->getError() ->hasError()
$t->diag('->getValue() ->getWidget() ->getParent() ->getError() ->hasError()');
$t->is($f->getWidget(), $widget, '->getWidget() returns the form field widget');
$t->is($f->getValue(), 'symfony', '->getValue() returns the form field value');
$t->is($f->getParent(), $parent, '->getParent() returns the form field parent');
$t->is($f->getError(), $error, '->getError() returns the form field error');
$t->is($f->hasError(), true, '->hasError() returns true if the form field has some error');

$errorSchema1 = new sfValidatorErrorSchema(new sfValidatorString());
$errorSchema1->addError($error = new sfValidatorError(new sfValidatorString(), 'error'), 'title1');
$parent1 = new sfFormField($schema, null, 'article', array('title' => 'symfony'), $errorSchema1);
$f1 = $parent1['title'];
$t->is($f1->hasError(), false, '->hasError() returns false if the form field has no error');

// __toString()
$t->diag('__toString()');
$t->is($f->__toString(), '<input type="text" name="article[title]" value="symfony" id="article_title" />', '__toString() renders the form field with default HTML attributes');

// ->render()
$t->diag('->render()');
$t->is($f->render(array('class' => 'foo')), '<input type="text" name="article[title]" value="symfony" class="foo" id="article_title" />', '->render() renders the form field');

// ->renderRow()
$t->diag('->renderRow()');
$output = <<<EOF
<tr>
  <th><label for="article_title">Title</label></th>
  <td>  <ul class="error_list">
    <li>error</li>
  </ul>
<input type="text" name="article[title]" value="symfony" id="article_title" /></td>
</tr>

EOF;
$t->is($f->renderRow(), $output, '->renderRow() renders a row');
$output = <<<EOF
<tr>
  <th><label for="article_title">Title</label></th>
  <td>  <ul class="error_list">
    <li>error</li>
  </ul>
<input type="text" name="article[title]" value="symfony" id="article_title" /><br />help</td>
</tr>

EOF;
$t->is($f->renderRow('help'), $output, '->renderRow() can take a help message');
try
{
  $parent->renderRow();
  $t->fail('->renderRow() throws an LogicException if the form field is a schema');
}
catch (LogicException $e)
{
  $t->pass('->renderRow() throws an LogicException if the form field is a schema');
}

// ->renderError();
$t->diag('->renderError()');
$output = <<<EOF
  <ul class="error_list">
    <li>error</li>
  </ul>

EOF;
$t->is($f->renderError(), $output, '->renderError() renders errors as HTML');
try
{
  $parent->renderError();
  $t->fail('->renderError() throws an LogicException if the form field is a schema');
}
catch (LogicException $e)
{
  $t->pass('->renderError() throws an LogicException if the form field is a schema');
}

// ->renderLabel()
$t->diag('->renderLabel()');
$t->is($f->renderLabel(), '<label for="article_title">Title</label>', '->renderLabel() renders the label as HTML');
try
{
  $parent->renderLabel();
  $t->fail('->renderLabel() throws an LogicException if the form field is a schema');
}
catch (LogicException $e)
{
  $t->pass('->renderLabel() throws an LogicException if the form field is a schema');
}

// ->renderLabelName()
$t->diag('->renderLabelName()');
$t->is($f->renderLabelName(), 'Title', '->renderLabelName() renders the label name');
try
{
  $parent->renderLabelName();
  $t->fail('->renderLabelName() throws an LogicException if the form field is a schema');
}
catch (LogicException $e)
{
  $t->pass('->renderLabelName() throws an LogicException if the form field is a schema');
}

// ->isHidden()
$t->diag('->isHidden()');
$t->is($f->isHidden(), false, '->isHidden() is a proxy method to the isHidden() method of the widget');
