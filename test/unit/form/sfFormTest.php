<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(60, new lime_output_color());

class FormTest extends sfForm
{
  public function getCSRFToken($secret)
  {
    return "*$secret*";
  }

  public function generateNameFormatForEmbedded($name, $nameFormat)
  {
    return parent::generateNameFormatForEmbedded($name, $nameFormat);
  }
}

sfForm::disableCSRFProtection();

// __construct()
$t->diag('__construct');
$f = new FormTest();
$t->ok($f->getValidatorSchema() instanceof sfValidatorSchema, '__construct() creates an empty validator schema');
$t->ok($f->getWidgetSchema() instanceof sfWidgetFormSchema, '__construct() creates an empty widget form schema');

$f = new sfForm(array('first_name' => 'Fabien'));
$t->is($f->getDefaults(), array('first_name' => 'Fabien'), '__construct() can take an array of default values as its first argument');

$f = new FormTest(array(), array(), 'secret');
$v = $f->getValidatorSchema();
$t->ok($f->isCSRFProtected(), '__construct() takes a CSRF secret as its second argument');
$t->is($v[sfForm::getCSRFFieldName()]->getOption('token'), '*secret*', '__construct() takes a CSRF secret as its second argument');

sfForm::enableCSRFProtection();
$f = new FormTest(array(), array(), false);
$t->ok(!$f->isCSRFProtected(), '__construct() can disable the CSRF protection by passing false as the second argument');

$f = new FormTest();
$t->ok($f->isCSRFProtected(), '__construct() uses CSRF protection if null is passed as the second argument and it\'s enabled globally');

// ->getOption() ->setOption()
$t->diag('->getOption() ->setOption()');
$f = new FormTest(array(), array('foo' => 'bar'));
$t->is($f->getOption('foo'), 'bar', '__construct takes an option array as its second argument');
$f->setOption('bar', 'foo');
$t->is($f->getOption('bar'), 'foo', '->setOption() changes the value of an option');

sfForm::disableCSRFProtection();

// ->setDefault() ->getDefault() ->hasDefault() ->setDefaults() ->getDefaults()
$t->diag('->setDefault() ->getDefault() ->hasDefault() ->setDefaults() ->getDefaults()');
$f = new FormTest();
$f->setDefaults(array('first_name' => 'Fabien'));
$t->is($f->getDefaults(), array('first_name' => 'Fabien'), 'setDefaults() sets the form default values');
$f->setDefault('last_name', 'Potencier');
$t->is($f->getDefaults(), array('first_name' => 'Fabien', 'last_name' => 'Potencier'), 'setDefault() sets a default value');
$t->is($f->hasDefault('first_name'), true, 'hasDefault() returns true if the form has a default value for the given field');
$t->is($f->hasDefault('name'), false, 'hasDefault() returns false if the form does not have a default value for the given field');
$t->is($f->getDefault('first_name'), 'Fabien', 'getDefault() returns a default value for a given field');
$t->is($f->getDefault('name'), null, 'getDefault() returns null if the form does not have a default value for a given field');

// ::enableCSRFProtection() ::disableCSRFProtection() ->isCSRFProtected()
$t->diag('::enableCSRFProtection() ::disableCSRFProtection()');
sfForm::enableCSRFProtection();
$f1 = new FormTest();
$t->ok($f1->isCSRFProtected(),'::enableCSRFProtection() enabled CSRF protection for all future forms');
sfForm::disableCSRFProtection();
$f2 = new FormTest();
$t->ok(!$f2->isCSRFProtected(),'::disableCSRFProtection() disables CSRF protection for all future forms');
$t->ok($f1->isCSRFProtected(),'::enableCSRFProtection() enabled CSRF protection for all future forms');
sfForm::enableCSRFProtection();
$t->ok(!$f2->isCSRFProtected(),'::disableCSRFProtection() disables CSRF protection for all future forms');

$f = new FormTest(array(), array(), false);
$t->ok(!$f->isCSRFProtected(),'->isCSRFProtected() returns true if the form is CSRF protected');

sfForm::enableCSRFProtection('mygreatsecret');
$f = new FormTest();
$v = $f->getValidatorSchema();
$t->is($v[sfForm::getCSRFFieldName()]->getOption('token'), '*mygreatsecret*', '::enableCSRFProtection() can take a secret argument');

// ::getCSRFFieldName() ::setCSRFFieldName()
$t->diag('::getCSRFFieldName() ::setCSRFFieldName()');
sfForm::setCSRFFieldName('_token_');
$f = new FormTest();
$v = $f->getValidatorSchema();
$t->ok(isset($v['_token_']), '::setCSRFFieldName() changes the CSRF token field name');
$t->is(sfForm::getCSRFFieldName(), '_token_', '::getCSRFFieldName() returns the CSRF token field name');

// ->isMultipart()
$t->diag('->isMultipart()');
$f = new FormTest();
$t->ok(!$f->isMultipart(),'->isMultipart() returns false if the form does not need a multipart form');
$f->setWidgetSchema(new sfWidgetFormSchema(array('image' => new sfWidgetFormInputFile())));
$t->ok($f->isMultipart(),'->isMultipart() returns true if the form needs a multipart form');

// ArrayAccess interface
$t->diag('ArrayAccess interface');
$f = new FormTest();
$f->setWidgetSchema(new sfWidgetFormSchema(array(
  'first_name' => new sfWidgetFormInput(),
  'last_name'  => new sfWidgetFormInput(),
  'image'      => new sfWidgetFormInputFile(),
)));
$f->setValidatorSchema(new sfValidatorSchema(array(
  'first_name' => new sfValidatorPass(),
)));
$t->ok($f['first_name'] instanceof sfFormField, 'sfForm implements the ArrayAccess interface');
try
{
  $f['first_name'] = 'first_name';
  $t->fail('sfForm ArrayAccess implementation does not permit to set a form field');
}
catch (LogicException $e)
{
  $t->pass('sfForm ArrayAccess implementation does not permit to set a form field');
}
$t->ok(isset($f['first_name']), 'sfForm implements the ArrayAccess interface');
unset($f['first_name']);
$t->ok(!isset($f['first_name']), 'sfForm implements the ArrayAccess interface');
$v = $f->getValidatorSchema();
$t->ok(!isset($v['first_name']), 'sfForm ArrayAccess implementation removes the widget and the validator');
$w = $f->getWidgetSchema();
$t->ok(!isset($w['first_name']), 'sfForm ArrayAccess implementation removes the widget and the validator');
try
{
  $f['nonexistant'];
  $t->fail('sfForm ArrayAccess implementation throws a LogicException if the form field does not exist');
}
catch (LogicException $e)
{
  $t->pass('sfForm ArrayAccess implementation throws a LogicException if the form field does not exist');
}

// ->bind() ->isValid() ->getValues() ->getValue() ->isBound() ->getErrorSchema()
$t->diag('->bind() ->isValid() ->getValues() ->isBound() ->getErrorSchema()');
$f = new FormTest();
$f->setWidgetSchema(new sfWidgetFormSchema(array(
  'first_name' => new sfWidgetFormInput(),
  'last_name'  => new sfWidgetFormInput(),
)));
$f->setValidatorSchema(new sfValidatorSchema(array(
  'first_name' => new sfValidatorString(array('min_length' => 2)),
  'last_name' => new sfValidatorString(array('min_length' => 2)),
)));
$t->ok(!$f->isBound(), '->isBound() returns false if the form is not bound');
$t->is($f->getValues(), array(), '->getValues() returns an empty array if the form is not bound');
$t->ok(!$f->isValid(), '->isValid() returns false if the form is not bound');

$t->is($f->getValue('first_name'), null, '->getValue() returns null if the form is not bound');
$f->bind(array('first_name' => 'Fabien', 'last_name' => 'Potencier'));
$t->ok($f->isBound(), '->isBound() returns true if the form is bound');
$t->is($f->getValues(), array('first_name' => 'Fabien', 'last_name' => 'Potencier'), '->getValues() returns an array of cleaned values if the form is bound');
$t->ok($f->isValid(), '->isValid() returns true if the form passes the validation');
$t->is($f->getValue('first_name'), 'Fabien', '->getValue() returns the cleaned value for a field name if the form is bound');

$f->bind(array());
$t->ok(!$f->isValid(), '->isValid() returns false if the form does not pass the validation');
$t->is($f->getValues(), array(), '->getValues() returns an empty array if the form does not pass the validation');
$t->is($f->getErrorSchema()->getMessage(), 'first_name [Required.] last_name [Required.]', '->getErrorSchema() returns an error schema object with all errors');

// ->generateNameFormatForEmbedded()
$t->diag('->generateNameFormatForEmbedded()');
$f = new FormTest();
$t->is($f->generateNameFormatForEmbedded('article', '%s'), 'article[%s]', '->generateNameFormatForEmbedded() generates a name format for an embed form');
$t->is($f->generateNameFormatForEmbedded('author', 'article[%s]'), 'article[author][%s]', '->generateNameFormatForEmbedded() generates a name format for an embed form');

// ->embedForm()
$t->diag('->embedForm()');
$author = new FormTest(array('first_name' => 'Fabien'));
$author->setWidgetSchema($author_widget_schema = new sfWidgetFormSchema(array('first_name' => new sfWidgetFormInput())));
$author->setValidatorSchema($author_validator_schema = new sfValidatorSchema(array('first_name' => new sfValidatorString(array('min_length' => 2)))));
$article = new FormTest();
$article->setWidgetSchema($article_widget_schema = new sfWidgetFormSchema(array('title' => new sfWidgetFormInput())));
$article->setValidatorSchema($article_validator_schema = new sfValidatorSchema(array('title' => new sfValidatorString(array('min_length' => 2)))));

$article->embedForm('author', $author);
$v = $article->getValidatorSchema();
$w = $article->getWidgetSchema();
$d = $article->getDefaults();

$t->is($v['author']['first_name'], $author_validator_schema['first_name'], '->embedForm() embeds the validator schema');
$t->is($w['author']['first_name'], $author_widget_schema['first_name'], '->embedForm() embeds the widget schema');
$t->is($d['author']['first_name'], 'Fabien', '->embedForm() merges default values from the embedded form');
$t->is($v['author'][sfForm::getCSRFFieldName()], null, '->embedForm() removes the CSRF token for the embedded form');
$t->is($w['author'][sfForm::getCSRFFieldName()], null, '->embedForm() removes the CSRF token for the embedded form');

// ->embedFormForEach()
$t->diag('->embedFormForEach()');
$article->embedFormForEach('authors', $author, 2);
$v = $article->getValidatorSchema();
$w = $article->getWidgetSchema();
$d = $article->getDefaults();

for ($i = 0; $i < 2; $i++)
{
  $t->is($v['authors'][$i]['first_name'], $author_validator_schema['first_name'], '->embedFormForEach() embeds the validator schema');
  $t->is($w['authors'][$i]['first_name'], $author_widget_schema['first_name'], '->embedFormForEach() embeds the widget schema');
  $t->is($d['authors'][$i]['first_name'], 'Fabien', '->embedFormForEach() merges default values from the embedded forms');
  $t->is($v['authors'][$i][sfForm::getCSRFFieldName()], null, '->embedFormForEach() removes the CSRF token for the embedded forms');
  $t->is($w['authors'][$i][sfForm::getCSRFFieldName()], null, '->embedFormForEach() removes the CSRF token for the embedded forms');
}
