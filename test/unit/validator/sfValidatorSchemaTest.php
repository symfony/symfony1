<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(43, new lime_output_color());

class PreValidator extends sfValidator
{
  protected function doClean($values)
  {
    if (isset($values['s1']) && isset($values['s2']))
    {
      throw new sfValidatorError($this, 's1_or_s2', array('value' => $values));
    }
  }
}

class PostValidator extends sfValidator
{
  protected function doClean($values)
  {
    foreach ($values as $key => $value)
    {
      $values[$key] = "*$value*";
    }

    return $values;
  }
}

class Post1Validator extends sfValidator
{
  protected function doClean($values)
  {
    if ($values['s1'] == $values['s2'])

    throw new sfValidatorError($this, 's1_not_equal_s2', array('value' => $values));
  }
}

$v1 = new sfValidatorString(array('max_length' => 3));
$v2 = new sfValidatorString(array('min_length' => 3));

// __construct()
$t->diag('__construct()');
$v = new sfValidatorSchema();
$t->is($v->getFields(), array(), '->__construct() can take no argument');
$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
$t->is($v->getFields(), array('s1' => $v1, 's2' => $v2), '->__construct() can take an array of named sfValidator objects');
try
{
  $v = new sfValidatorSchema('string');
  $t->fail('__construct() throws an exception when passing a non supported first argument');
}
catch (sfException $e)
{
  $t->pass('__construct() throws an exception when passing a non supported first argument');
}

// implements ArrayAccess
$t->diag('implements ArrayAccess');
$v = new sfValidatorSchema();
$v['s1'] = $v1;
$v['s2'] = $v2;
$t->is($v->getFields(), array('s1' => $v1, 's2' => $v2), 'sfValidatorSchema implements the ArrayAccess interface for the fields');

try
{
  $v['v1'] = 'string';
  $t->fail('sfValidatorSchema implements the ArrayAccess interface for the fields');
}
catch (sfException $e)
{
  $t->pass('sfValidatorSchema implements the ArrayAccess interface for the fields');
}

$v = new sfValidatorSchema(array('s1' => $v1));
$t->is(isset($v['s1']), true, 'sfValidatorSchema implements the ArrayAccess interface for the fields');
$t->is(isset($v['s2']), false, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

$v = new sfValidatorSchema(array('s1' => $v1));
$t->is($v['s1'], $v1, 'sfValidatorSchema implements the ArrayAccess interface for the fields');
$t->is($v['s2'], null, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

$v = new sfValidatorSchema(array('v1' => $v1));
unset($v['s1']);
$t->is($v['s1'], null, 'sfValidatorSchema implements the ArrayAccess interface for the fields');

// ->configure()
$t->diag('->configure()');
$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
$t->is($v->getOption('allow_extra_fields'), false, '->configure() sets "allow_extra_fields" option to false by default');
$t->is($v->getOption('filter_extra_fields'), true, '->configure() sets "filter_extra_fields" option to true by default');
$t->is($v->getMessage('extra_fields'), 'Extra field %field%.', '->configure() has a default error message for the "extra_fields" error');

$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true, 'filter_extra_fields' => false), array('extra_fields' => 'Extra fields'));
$t->is($v->getOption('allow_extra_fields'), true, '->__construct() can override the default value for the "allow_extra_fields" option');
$t->is($v->getOption('filter_extra_fields'), false, '->__construct() can override the default value for the "filter_extra_fields" option');

$t->is($v->getMessage('extra_fields'), 'Extra fields', '->__construct() can override the default message for the "extra_fields" error message');

// ->clean()
$t->diag('->clean()');

$v = new sfValidatorSchema();
$t->is($v->clean(null), array(), '->clean() converts null to empty array before validation');

$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));

try
{
  $v->clean('foo');
  $t->fail('->clean() throws an sfException exception if the first argument is not an array of value');
}
catch (sfException $e)
{
  $t->pass('->clean() throws an sfException exception if the first argument is not an array of value');
}

$t->is($v->clean(array('s1' => 'foo', 's2' => 'bar')), array('s1' => 'foo', 's2' => 'bar'), '->clean() returns the string unmodified');

try
{
  $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if a you give a non existant field');
  $t->skip('', 3);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if a you give a non existant field');
  $t->is(count($e), 1, '->clean() throws an exception with all error messages');
  $t->is($e[0]->getCode(), 'extra_fields', '->clean() throws an exception with all error messages');
}

$t->diag('required fields');
try
{
  $v->clean(array('s1' => 'foo'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if a required field is not provided');
  $t->skip('', 3);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if a required field is not provided');
  $t->is(count($e), 1, '->clean() throws an exception with all error messages');
  $t->is($e['s2']->getCode(), 'required', '->clean() throws an exception with all error messages');
}

$t->diag('pre validators');
$v1 = new sfValidatorString(array('max_length' => 3, 'required' => false));
$v2 = new sfValidatorString(array('min_length' => 3, 'required' => false));
$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2, '_pre_validator' => new PreValidator()));
try
{
  $v->clean(array('s1' => 'foo', 's2' => 'bar'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if a _pre_validator fails');
  $t->skip('', 2);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if a _pre_validator fails');
  $t->is(count($e), 1, '->clean() throws an exception with all error messages');
  $t->is($e[0]->getCode(), 's1_or_s2', '->clean() throws an exception with all error messages');
}

$t->diag('post validators');
$v1 = new sfValidatorString(array('max_length' => 3, 'required' => false));
$v2 = new sfValidatorString(array('min_length' => 3, 'required' => false));
$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2, '_post_validator' => new PostValidator()));
$t->is($v->clean(array('s1' => 'foo', 's2' => 'bar')), array('s1' => '*foo*', 's2' => '*bar*'), '->clean() executes post validators');

$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2, '_post_validator' => new Post1Validator()));
try
{
  $v->clean(array('s1' => 'foo', 's2' => 'foo'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if a _post_validator fails');
  $t->skip('', 2);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if a _post_validator fails');
  $t->is(count($e), 1, '->clean() throws an exception with all error messages');
  $t->is($e[0]->getCode(), 's1_not_equal_s2', '->clean() throws an exception with all error messages');
}

$t->diag('extra fields');
$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2));
$v->setOption('allow_extra_fields', true);
$ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
$t->is($ret, array('s1' => 'foo', 's2' => 'bar'), '->clean() filters non existant fields if "allow_extra_fields" is true');

$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true));
$ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
$t->is($ret, array('s1' => 'foo', 's2' => 'bar'), '->clean() filters non existant fields if "allow_extra_fields" is true');

$v = new sfValidatorSchema(array('s1' => $v1, 's2' => $v2), array('allow_extra_fields' => true, 'filter_extra_fields' => false));
$ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
$t->is($ret, array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'), '->clean() do not filter non existant fields if "filter_extra_fields" is false');

$v->setOption('filter_extra_fields', false);
$ret = $v->clean(array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'));
$t->is($ret, array('s1' => 'foo', 's2' => 'bar', 'foo' => 'bar'), '->clean() do not filter non existant fields if "filter_extra_fields" is false');

$t->diag('one validator fails');
$v2->setOption('max_length', 2);
try
{
  $v->clean(array('s1' => 'foo', 's2' => 'bar'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
  $t->skip('', 2);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
  $t->is(count($e), 1, '->clean() throws an exception with all error messages');
  $t->is($e['s2']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
}

$t->diag('several validators fail');
$v1->setOption('max_length', 2);
$v2->setOption('max_length', 2);
try
{
  $v->clean(array('s1' => 'foo', 's2' => 'bar'));
  $t->fail('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
  $t->skip('', 3);
}
catch (sfValidatorErrorSchema $e)
{
  $t->pass('->clean() throws an sfValidatorErrorSchema exception if one of the validators fails');
  $t->is(count($e), 2, '->clean() throws an exception with all error messages');
  $t->is($e['s2']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
  $t->is($e['s1']->getCode(), 'max_length', '->clean() throws an exception with all error messages');
}
