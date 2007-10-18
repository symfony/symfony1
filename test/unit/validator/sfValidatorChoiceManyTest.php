<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$v = new sfValidatorChoiceMany(array('foo', 'bar'));

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), array('foo'), '->clean() checks that the value is an expected value');
$t->is($v->clean(array('foo')), array('foo'), '->clean() checks that the value is an expected value');
$t->is($v->clean(array('foo', 'bar')), array('foo', 'bar'), '->clean() checks that the value is an expected value');

try
{
  $v->clean('foobar');
  $t->fail('->clean() throws an sfValidatorError if the value is not an expected value');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value is not an expected value');
}

try
{
  $v->clean(array('foobar', 'bar'));
  $t->fail('->clean() throws an sfValidatorError if the value is not an expected value');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value is not an expected value');
}
