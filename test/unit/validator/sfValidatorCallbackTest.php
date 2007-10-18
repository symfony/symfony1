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

function clean_test($validator, $value)
{
  if ($value != 'foo')
  {
    throw new sfValidatorError($validator, 'must_be_foo');
  }

  return "*$value*";
}

$v = new sfValidatorCallback('clean_test');

// ->clean()
$t->diag('->clean()');
$t->is($v->clean('foo'), '*foo*', '->clean() calls our validator callback');
try
{
  $v->clean('bar');
  $t->fail('->clean() calls our validator callback');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() calls our validator callback');
}

// ->configure()
$t->diag('->configure()');
$t->is($v->clean(''), null, '->configure() switch required to false by default');
