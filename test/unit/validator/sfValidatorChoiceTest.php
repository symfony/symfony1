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

// __construct()
$t->diag('__construct()');
try
{
  new sfValidatorChoice();
  $t->fail('__construct() throws an sfException if you don\'t pass an expected option');
}
catch (sfException $e)
{
  $t->pass('__construct() throws an sfException if you don\'t pass an expected option');
}

$v = new sfValidatorChoice(array('choices' => array('foo', 'bar')));

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), 'foo', '->clean() checks that the value is an expected value');
$t->is($v->clean('bar'), 'bar', '->clean() checks that the value is an expected value');

try
{
  $v->clean('foobar');
  $t->fail('->clean() throws an sfValidatorError if the value is not an expected value');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value is not an expected value');
}

// ->asString()
$t->diag('->asString()');
$t->is($v->asString(), 'Choice({ choices: [foo, bar] })', '->asString() returns a string representation of the validator');
