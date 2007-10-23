<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(12, new lime_output_color());

$v1 = new sfValidatorString(array('max_length' => 3));
$v2 = new sfValidatorString(array('min_length' => 3));

$v = new sfValidatorAll(array($v1, $v2));

// __construct()
$t->diag('__construct()');
$v = new sfValidatorAll();
$t->is($v->getValidators(), array(), '->__construct() can take no argument');
$v = new sfValidatorAll($v1);
$t->is($v->getValidators(), array($v1), '->__construct() can take a validator as its first argument');
$v = new sfValidatorAll(array($v1, $v2));
$t->is($v->getValidators(), array($v1, $v2), '->__construct() can take an array of validators as its first argument');
try
{
  $v = new sfValidatorAll('string');
  $t->fail('__construct() throws an exception when passing a non supported first argument');
}
catch (sfException $e)
{
  $t->pass('__construct() throws an exception when passing a non supported first argument');
}

// ->addValidator()
$t->diag('->addValidator()');
$v = new sfValidatorAll();
$v->addValidator($v1);
$v->addValidator($v2);
$t->is($v->getValidators(), array($v1, $v2), '->addValidator() adds a validator');

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), 'foo', '->clean() returns the string unmodified');

$v2->setOption('max_length', 2);
try
{
  $v->clean('foo');
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 2);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is($e[0]->getCode(), 'max_length', '->clean() throws a sfValidatorSchemaError');
  $t->is($e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorSchemaError');
}

try
{
  $v->setMessage('invalid', 'Invalid.');
  $v->clean('foo');
  $t->fail('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->skip('', 2);
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError exception if one of the validators fails');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError if invalid message is not empty');
  $t->is(!$e instanceof sfValidatorErrorSchema, 'max_length', '->clean() throws a sfValidatorError if invalid message is not empty');
}
