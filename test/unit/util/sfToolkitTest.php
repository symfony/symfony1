<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/util/sfToolkit.class.php');

$t = new lime_test(13, new lime_output_color());

// ::stringToArray()
$t->diag('::stringToArray()');
$tests = array(
  'foo=bar' => array('foo' => 'bar'),
  'foo1=bar1 foo=bar   ' => array('foo1' => 'bar1', 'foo' => 'bar'),
  'foo1="bar1 foo1"' => array('foo1' => 'bar1 foo1'),
  'foo1="bar1 foo1" foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1 = "bar1=foo1" foo=bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1= \'bar1 foo1\'    foo  =     bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1=\'bar1=foo1\' foo = bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1=  bar1 foo1 foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1="l\'autre" foo=bar' => array('foo1' => 'l\'autre', 'foo' => 'bar'),
  'foo1="l"autre" foo=bar' => array('foo1' => 'l"autre', 'foo' => 'bar'),
  'foo_1=bar_1' => array('foo_1' => 'bar_1'),
);

foreach ($tests as $string => $attributes)
{
  $t->is(sfToolkit::stringToArray($string), $attributes, '->stringToArray()');
}

// ::isUTF8()
$t->diag('::isUTF8()');
$t->is('été', true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'), true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'.chr(1)), false, '::isUTF8() returns false if the parameter is not an UTF-8 encoded string');
