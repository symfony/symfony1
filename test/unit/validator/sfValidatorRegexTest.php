<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

$v = new sfValidatorRegex('/^[0-9]+$/');

// ->getErrorCodes()
$t->diag('->getErrorCodes()');
$t->is($v->getErrorCodes(), array('required', 'invalid'), '->getErrorCodes() returns all possible error codes');

// ->clean()
$t->diag('->clean()');
$t->is($v->clean(12), '12', '->clean() checks that the value match the regex');

try
{
  $v->clean('symfony');
  $t->fail('->clean() throws an sfValidatorError if the value does not match the pattern');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the value does not match the pattern');
}
