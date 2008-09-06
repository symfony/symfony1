<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(102, new lime_output_color());

class FormTest extends sfForm
{
  public function getCSRFToken($secret)
  {
    return "*$secret*";
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

sfForm::enableCSRFProtection('*mygreatsecret*');
$f = new FormTest();
$f->setDefaults(array('first_name' => 'Fabien'));
$t->is($f->getDefault('_csrf_token'), $f->getCSRFToken('*mygreatsecret*'), '->getDefaults() keeps the CSRF token default value');
sfForm::disableCSRFProtection();

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
$t->ok(!$f->isCSRFProtected(), '->isCSRFProtected() returns true if the form is CSRF protected');

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

// ->setValidators() ->setValidatorSchema() ->getValidatorSchema()
$t->diag('->setValidators() ->setValidatorSchema() ->getValidatorSchema()');
$f = new FormTest();
$validators = array(
  'first_name' => new sfValidatorPass(),
  'last_name' => new sfValidatorPass(),
);
$validatorSchema = new sfValidatorSchema($validators);
$f->setValidatorSchema($validatorSchema);
$t->is_deeply($f->getValidatorSchema(), $validatorSchema, '->setValidatorSchema() sets the current validator schema');
$f->setValidators($validators);
$schema = $f->getValidatorSchema();
$t->ok($schema['first_name'] == $validators['first_name'], '->setValidators() sets field validators');
$t->ok($schema['last_name'] == $validators['last_name'], '->setValidators() sets field validators');

// ->setWidgets() ->setWidgetSchema() ->getWidgetSchema()
$t->diag('->setWidgets() ->setWidgetSchema() ->getWidgetSchema()');
$f = new FormTest();
$widgets = array(
  'first_name' => new sfWidgetFormInput(),
  'last_name'  => new sfWidgetFormInput(),
);
$widgetSchema = new sfWidgetFormSchema($widgets);
$f->setWidgetSchema($widgetSchema);
$t->ok($f->getWidgetSchema() == $widgetSchema, '->setWidgetSchema() sets the current widget schema');
$f->setWidgets($widgets);
$schema = $f->getWidgetSchema();
$t->ok($schema['first_name'] == $widgets['first_name'], '->setWidgets() sets field widgets');
$t->ok($schema['last_name'] == $widgets['last_name'], '->setWidgets() sets field widgets');

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
$t->is($f->getValue('nonsense'), null, '->getValue() returns null when non-existant param is requested');

$f->bind(array());
$t->ok(!$f->isValid(), '->isValid() returns false if the form does not pass the validation');
$t->is($f->getValues(), array(), '->getValues() returns an empty array if the form does not pass the validation');
$t->is($f->getErrorSchema()->getMessage(), 'first_name [Required.] last_name [Required.]', '->getErrorSchema() returns an error schema object with all errors');

$t->diag('bind when field names are numeric');
$f = new FormTest();
$f->setValidatorSchema(new sfValidatorSchema(array(
  1 => new sfValidatorString(array('min_length' => 2)),
  2 => new sfValidatorString(array('min_length' => 2)),
)));
$f->bind(array(1 => 'fabien', 2 => 'potencier'));
$t->ok($f->isValid(), '->bind() behaves correctly when field names are numeric');

$t->diag('bind with files');
$f = new FormTest();
$f->setValidatorSchema(new sfValidatorSchema(array(
  1 => new sfValidatorString(array('min_length' => 2)),
  2 => new sfValidatorString(array('min_length' => 2)),
  'file' => new sfValidatorFile(array('max_size' => 2)),
)));
$f->setWidgetSchema(new sfWidgetFormSchema(array('file' => new sfWidgetFormInputFile())));
$f->bind(array(1 => 'f', 2 => 'potencier'), array(
  'file' => array('name' => 'test1.txt', 'type' => 'text/plain', 'tmp_name' => '/tmp/test1.txt', 'error' => 0, 'size' => 100)
));
$t->is($f->getErrorSchema()->getCode(), '1 [min_length] file [max_size]', '->bind() behaves correctly with files');

try
{
  $f->bind(array(1 => 'f', 2 => 'potencier'));
  $t->fail('->bind() second argument is mandatory if the form is multipart');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->bind() second argument is mandatory if the form is multipart');
}

$t->diag('bind with files in embed form');
$pf = new FormTest(); //parent form
$pf->setValidatorSchema(new sfValidatorSchema()); //cleaning sfValidatorSchema to silence `_token_`

$ef = new FormTest(); //embed form

$ef->setValidatorSchema(new sfValidatorSchema(array(
  1 => new sfValidatorString(array('min_length' => 2)),
  2 => new sfValidatorString(array('min_length' => 2)),
  'file' => new sfValidatorFile(array('max_size' => 2)),
)));
$ef->setWidgetSchema(new sfWidgetFormSchema(array('file' => new sfWidgetFormInputFile())));
$pf->embedForm('ef', $ef);
$pf->bind(array('ef' => array(1 => 'f', 2 => 'potencier')), array('ef' => array(
  'file' => array('name' => 'test1.txt', 'type' => 'text/plain', 'tmp_name' => '/tmp/test1.txt', 'error' => 0, 'size' => 100)
)));
$t->is($pf->getErrorSchema()->getCode(), 'ef [1 [min_length] file [max_size]]', '->bind() behaves correctly with files in embed form');


// ->renderGlobalErrors()
$t->diag('->renderGlobalErrors()');
$f = new FormTest();
$f->setValidatorSchema(new sfValidatorSchema(array(
  'id'         => new sfValidatorInteger(),
  'first_name' => new sfValidatorString(array('min_length' => 2)),
  'last_name'  => new sfValidatorString(array('min_length' => 2)),
)));
$f->setWidgetSchema(new sfWidgetFormSchema(array(
  'id'         => new sfWidgetFormInputHidden(),
  'first_name' => new sfWidgetFormInput(),
  'last_name'  => new sfWidgetFormInput(),
)));
$f->bind(array(
  'id'         => 'dddd',
  'first_name' => 'f',
  'last_name'  => 'potencier',
));
$output = <<<EOF
  <ul class="error_list">
    <li>Id: "dddd" is not an integer.</li>
  </ul>

EOF;
$t->is($f->renderGlobalErrors(), $output, '->renderGlobalErrors() renders global errors as an HTML list');

// ->render()
$t->diag('->render()');
$f = new FormTest();
$f->setValidators(array(
  'id'         => new sfValidatorInteger(),
  'first_name' => new sfValidatorString(array('min_length' => 2)),
  'last_name'  => new sfValidatorString(array('min_length' => 2)),
));
$f->setWidgets(array(
  'id'         => new sfWidgetFormInputHidden(),
  'first_name' => new sfWidgetFormInput(),
  'last_name'  => new sfWidgetFormInput(),
));
$f->bind(array(
  'id'         => '1',
  'first_name' => 'Fabien',
  'last_name'  => 'Potencier',
));
$output = <<<EOF
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="1" id="id" /></td>
</tr>

EOF;
$t->is($f->__toString(), $output, '->__toString() renders the form as HTML');
$output = <<<EOF
<tr>
  <th><label for="first_name">First name</label></th>
  <td><input type="text" name="first_name" value="Fabien" class="foo" id="first_name" /></td>
</tr>
<tr>
  <th><label for="last_name">Last name</label></th>
  <td><input type="text" name="last_name" value="Potencier" id="last_name" /><input type="hidden" name="id" value="1" id="id" /></td>
</tr>

EOF;
$t->is($f->render(array('first_name' => array('class' => 'foo'))), $output, '->render() renders the form as HTML');

// ->embedForm()
$t->diag('->embedForm()');

$author = new FormTest(array('first_name' => 'Fabien'));
$author->setWidgetSchema($author_widget_schema = new sfWidgetFormSchema(array('first_name' => new sfWidgetFormInput())));
$author->setValidatorSchema($author_validator_schema = new sfValidatorSchema(array('first_name' => new sfValidatorString(array('min_length' => 2)))));

$company = new FormTest();
$company->setWidgetSchema($company_widget_schema = new sfWidgetFormSchema(array('name' => new sfWidgetFormInput())));
$company->setValidatorSchema($company_validator_schema = new sfValidatorSchema(array('name' => new sfValidatorString(array('min_length' => 2)))));

$article = new FormTest();
$article->setWidgetSchema($article_widget_schema = new sfWidgetFormSchema(array('title' => new sfWidgetFormInput())));
$article->setValidatorSchema($article_validator_schema = new sfValidatorSchema(array('title' => new sfValidatorString(array('min_length' => 2)))));

$author->embedForm('company', $company);
$article->embedForm('author', $author);
$v = $article->getValidatorSchema();
$w = $article->getWidgetSchema();
$d = $article->getDefaults();

$w->setNameFormat('article[%s]');

$t->ok($v['author']['first_name'] == $author_validator_schema['first_name'], '->embedForm() embeds the validator schema');
$t->ok($w['author']['first_name'] == $author_widget_schema['first_name'], '->embedForm() embeds the widget schema');
$t->is($d['author']['first_name'], 'Fabien', '->embedForm() merges default values from the embedded form');
$t->is($v['author'][sfForm::getCSRFFieldName()], null, '->embedForm() removes the CSRF token for the embedded form');
$t->is($w['author'][sfForm::getCSRFFieldName()], null, '->embedForm() removes the CSRF token for the embedded form');

$t->is($w['author']->generateName('first_name'), 'article[author][first_name]', '->embedForm() changes the name format to reflect the embedding');
$t->is($w['author']['company']->generateName('name'), 'article[author][company][name]', '->embedForm() changes the name format to reflect the embedding');

// ->embedFormForEach()
$t->diag('->embedFormForEach()');
$article->embedFormForEach('authors', $author, 2);
$v = $article->getValidatorSchema();
$w = $article->getWidgetSchema();
$d = $article->getDefaults();
$w->setNameFormat('article[%s]');

for ($i = 0; $i < 2; $i++)
{
  $t->ok($v['authors'][$i]['first_name'] == $author_validator_schema['first_name'], '->embedFormForEach() embeds the validator schema');
  $t->ok($w['authors'][$i]['first_name'] == $author_widget_schema['first_name'], '->embedFormForEach() embeds the widget schema');
  $t->is($d['authors'][$i]['first_name'], 'Fabien', '->embedFormForEach() merges default values from the embedded forms');
  $t->is($v['authors'][$i][sfForm::getCSRFFieldName()], null, '->embedFormForEach() removes the CSRF token for the embedded forms');
  $t->is($w['authors'][$i][sfForm::getCSRFFieldName()], null, '->embedFormForEach() removes the CSRF token for the embedded forms');
}

$t->is($w['authors'][0]->generateName('first_name'), 'article[authors][0][first_name]', '->embedFormForEach() changes the name format to reflect the embedding');

// ::convertFileInformation()
$t->diag('::convertFileInformation()');
$input = array(
  'file' => array(
    'name' => 'test1.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/test1.txt',
    'error' => 0,
    'size' => 100,
  ),
  'file1' => array(
    'name' => 'test2.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/test1.txt',
    'error' => 0,
    'size' => 200,
  ),
);
$t->is_deeply(sfForm::convertFileInformation($input), $input, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');

$input = array(
  'article' => array(
    'name' => array(
      'file1' => 'test1.txt',
      'file2' => 'test2.txt',
    ),
    'type' => array(
      'file1' => 'text/plain',
      'file2' => 'text/plain',
    ),
    'tmp_name' => array(
      'file1' => '/tmp/test1.txt',
      'file2' => '/tmp/test2.txt',
    ),
    'error' => array(
      'file1' => 0,
      'file2' => 0,
    ),
    'size' => array(
      'file1' => 100,
      'file2' => 200,
    ),
  ),
);
$expected = array(
  'article' => array(
    'file1' => array(
      'name' => 'test1.txt',
      'type' => 'text/plain',
      'tmp_name' => '/tmp/test1.txt',
      'error' => 0,
      'size' => 100,
    ),
    'file2' => array(
      'name' => 'test2.txt',
      'type' => 'text/plain',
      'tmp_name' => '/tmp/test2.txt',
      'error' => 0,
      'size' => 200,
    ),
  ),
);
$t->is_deeply(sfForm::convertFileInformation($input), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
$t->is_deeply(sfForm::convertFileInformation($expected), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');

$input = array(
  'article' => array(
    'name' => array(
      'files' => array(
        'file1' => 'test1.txt',
        'file2' => 'test2.txt',
      ),
    ),
    'type' => array(
      'files' => array(
        'file1' => 'text/plain',
        'file2' => 'text/plain',
      ),
    ),
    'tmp_name' => array(
      'files' => array(
        'file1' => '/tmp/test1.txt',
        'file2' => '/tmp/test2.txt',
      ),
    ),
    'error' => array(
      'files' => array(
        'file1' => 0,
        'file2' => 0,
      ),
    ),
    'size' => array(
      'files' => array(
        'file1' => 100,
        'file2' => 200,
      ),
    ),
  ),
);
$expected = array(
  'article' => array(
    'files' => array(
      'file1' => array(
        'name' => 'test1.txt',
        'type' => 'text/plain',
        'tmp_name' => '/tmp/test1.txt',
        'error' => 0,
        'size' => 100,
      ),
      'file2' => array(
        'name' => 'test2.txt',
        'type' => 'text/plain',
        'tmp_name' => '/tmp/test2.txt',
        'error' => 0,
        'size' => 200,
      ),
    )
  ),
);
$t->is_deeply(sfForm::convertFileInformation($input), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
$t->is_deeply(sfForm::convertFileInformation($expected), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');

$input = array(
  'name' => array(
    'file1' => 'test1.txt',
    'file2' => 'test2.txt',
  ),
  'type' => array(
    'file1' => 'text/plain',
    'file2' => 'text/plain',
  ),
  'tmp_name' => array(
    'file1' => '/tmp/test1.txt',
    'file2' => '/tmp/test2.txt',
  ),
  'error' => array(
    'file1' => 0,
    'file2' => 0,
  ),
  'size' => array(
    'file1' => 100,
    'file2' => 200,
  ),
);
$expected = array(
  'file1' => array(
    'name' => 'test1.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/test1.txt',
    'error' => 0,
    'size' => 100,
  ),
  'file2' => array(
    'name' => 'test2.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/test2.txt',
    'error' => 0,
    'size' => 200,
  ),
);
$t->is_deeply(sfForm::convertFileInformation($input), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');
$t->is_deeply(sfForm::convertFileInformation($expected), $expected, '::convertFileInformation() converts $_FILES to be coherent with $_GET and $_POST naming convention');

// ->renderFormTag()
$t->diag('->renderFormTag()');
$f = new FormTest();
$t->is($f->renderFormTag('/url'), '<form action="/url" method="POST">', '->renderFormTag() renders the form tag');
$t->is($f->renderFormTag('/url', array('method' => 'PUT')), '<form method="POST" action="/url"><input type="hidden" name="sf_method" value="PUT" />', '->renderFormTag() adds a hidden input tag if the method is not GET or POST');
$f->setWidgetSchema(new sfWidgetFormSchema(array('image' => new sfWidgetFormInputFile())));
$t->is($f->renderFormTag('/url'), '<form action="/url" method="POST" enctype="multipart/form-data">', '->renderFormTag() adds the enctype attribute if the form is multipart');

// __clone()
$t->diag('__clone()');
$a = new FormTest();
$a->setValidatorSchema(new sfValidatorSchema(array(
  'first_name' => new sfValidatorString(array('min_length' => 2)),
)));
$a->bind(array('first_name' => 'F'));
$a1 = clone $a;

$t->ok($a1->getValidatorSchema() !== $a->getValidatorSchema(), '__clone() clones the validator schema');
$t->ok($a1->getValidatorSchema() == $a->getValidatorSchema(), '__clone() clones the validator schema');

$t->ok($a1->getWidgetSchema() !== $a->getWidgetSchema(), '__clone() clones the widget schema');
$t->ok($a1->getWidgetSchema() == $a->getWidgetSchema(), '__clone() clones the widget schema');

$t->ok($a1->getErrorSchema() !== $a->getErrorSchema(), '__clone() clones the error schema');
$t->ok($a1->getErrorSchema()->getMessage() == $a->getErrorSchema()->getMessage(), '__clone() clones the error schema');

// mergeForm()
$t->diag('mergeForm()');

class TestForm1 extends FormTest
{
  public function configure()
  {
    $this->disableCSRFProtection();
    $this->setWidgets(array(
      'a' => new sfWidgetFormInput(),
      'b' => new sfWidgetFormInput(),
      'c' => new sfWidgetFormInput(),
    ));
    $this->setValidators(array(
      'a' => new sfValidatorString(array('min_length' => 2)),
      'b' => new sfValidatorString(array('max_length' => 3)),
      'c' => new sfValidatorString(array('max_length' => 1000)),
    ));
  }
}

class TestForm2 extends FormTest
{
  public function configure()
  {
    $this->disableCSRFProtection();
    $this->setWidgets(array(
      'c' => new sfWidgetFormTextarea(),
      'd' => new sfWidgetFormTextarea(),
    ));
    $this->setValidators(array(
      'c' => new sfValidatorPass(),
      'd' => new sfValidatorString(array('max_length' => 5)),
    ));
    $this->validatorSchema->setPreValidator(new sfValidatorPass());
    $this->validatorSchema->setPostValidator(new sfValidatorPass());
  }
}

$f1 = new TestForm1();
$f2 = new TestForm2();
$f1->mergeForm($f2);

$widgetSchema = $f1->getWidgetSchema();
$validatorSchema = $f1->getValidatorSchema();
$t->is(count($widgetSchema->getFields()), 4, 'mergeForm() merges a widget form schema');
$t->is(count($validatorSchema->getFields()), 4, 'mergeForm() merges a validator schema');
$t->is(array_keys($widgetSchema->getFields()), array('a', 'b', 'c', 'd'), 'mergeForms() merges the correct widgets');
$t->is(array_keys($validatorSchema->getFields()), array('a', 'b', 'c', 'd'), 'mergeForms() merges the correct validators');
$t->isa_ok($widgetSchema['c'], 'sfWidgetFormTextarea', 'mergeForm() overrides original form widget');
$t->isa_ok($validatorSchema['c'], 'sfValidatorPass', 'mergeForm() overrides original form validator');
$t->isa_ok($validatorSchema->getPreValidator(), 'sfValidatorPass', 'mergeForm() merges pre validator');
$t->isa_ok($validatorSchema->getPostValidator(), 'sfValidatorPass', 'mergeForm() merges post validator');

try
{
  $f1->bind(array('a' => 'foo', 'b' => 'bar', 'd' => 'far_too_long_value'));
  $f1->mergeForm($f2);
  $t->fail('mergeForm() disallows merging already bound forms');
}
catch (LogicException $e)
{
  $t->pass('mergeForm() disallows merging already bound forms');
}

$errorSchema = $f1->getErrorSchema();
$t->ok(array_key_exists('d', $errorSchema->getErrors()), 'mergeForm() merges errors after having been bound');
