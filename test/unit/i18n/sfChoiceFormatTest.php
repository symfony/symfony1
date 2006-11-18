<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(24, new lime_output_color());

$n = new sfChoiceFormat();

$strings = array(
  array(
    '[1,2] accepts values between 1 and 2, inclusive',
    array(
      array('[1,2]'),
      array('accepts values between 1 and 2, inclusive'),
    ),
  ),

  array(
    '(1,2) accepts values between 1 and 2, excluding 1 and 2',
    array(
      array('(1,2)'),
      array('accepts values between 1 and 2, excluding 1 and 2'),
    ),
  ),

  array(
    '{1,2,3,4} only values defined in the set are accepted',
    array(
      array('{1,2,3,4}'),
      array('only values defined in the set are accepted'),
    ),
  ),

  array(
    '[-Inf,0) accepts value greater or equal to negative infinity and strictly less than 0',
    array(
      array('[-Inf,0)'),
      array('accepts value greater or equal to negative infinity and strictly less than 0'),
    ),
  ),

  array(
    '[0] no file|[1] one file|(1,Inf] {number} files',
    array(
      array('[0]', '[1]', '(1,Inf]'),
      array('no file', 'one file', '{number} files'),
    ),
  ),
);

// ->parse()
$t->diag('->parse()');
foreach ($strings as $string)
{
  $t->is($n->parse($string[0]), $string[1], '->parse() takes a choice strings as its first parameters');
}

// ->isValid()
$t->diag('->isValid()');
$t->is($n->isValid(1, '[1]'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(2, '[1]'), false, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(1, '(1)'), false, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(1, '(1,10)'), false, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(10, '(1,10)'), false, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(4, '(1,10)'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(1, '{1,2,4,5}'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(3, '{1,2,4,5}'), false, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(4, '{1,2,4,5}'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(1, '[0,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(10000000, '[0,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(10000000, '[0,Inf]'), true, '->isValid() determines if a given number belongs to the given set');
$t->is($n->isValid(-10000000, '[-Inf,+Inf]'), true, '->isValid() determines if a given number belongs to the given set');

try
{
  $n->isValid(1, '[1');
  $t->fail('->isValid() throw an exception if the set is not valid');
}
catch (sfException $e)
{
  $t->pass('->isValid() throw an exception if the set is not valid');
}

// ->format()
$t->diag('->format()');
$t->is($n->format($strings[0][0], 1), $strings[0][1][1][0], '->format() returns the string that match the number');
$t->is($n->format($strings[0][0], 4), false, '->format() returns the string that match the number');
$t->is($n->format($strings[4][0], 0), $strings[4][1][1][0], '->format() returns the string that match the number');
$t->is($n->format($strings[4][0], 1), $strings[4][1][1][1], '->format() returns the string that match the number');
$t->is($n->format($strings[4][0], 12), $strings[4][1][1][2], '->format() returns the string that match the number');
