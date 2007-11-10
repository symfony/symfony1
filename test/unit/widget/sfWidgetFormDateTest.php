<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(22, new lime_output_color());

$w = new sfWidgetFormDate();

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');

foreach (array(
  '2005-10-15' => array('year' => 2005, 'month' => 10, 'day' => 15),
  time() => array('year' => date('Y'), 'month' => date('m'), 'day' => date('d')),
  'tomorrow' => array('year' => date('Y', time() + 86400), 'month' => date('m', time() + 86400), 'day' => date('d', time() + 86400)),
) as $date => $values)
{
  $dom->loadHTML($w->render('foo', $date));
  $css = new sfDomCssSelector($dom);

  // selected date
  $t->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
  $t->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
  $t->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');
}

$values = array('year' => 2005, 'month' => 10, 'day' => 15);
$dom->loadHTML($w->render('foo', $values));
$css = new sfDomCssSelector($dom);

// selected date
$t->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
$t->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
$t->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');

$dom->loadHTML($w->render('foo', '2005-10-15'));
$css = new sfDomCssSelector($dom);

// number of options in each select
$t->is(count($css->matchAll('#foo_year option')->getNodes()), 11, '->render() renders a select tag for the 10 years around the current one');
$t->is(count($css->matchAll('#foo_month option')->getNodes()), 12, '->render() renders a select tag for the 12 months in a year');
$t->is(count($css->matchAll('#foo_day option')->getNodes()), 31, '->render() renders a select tag for the 31 days in a month');

// separator option
$t->diag('separator option');
$t->is($css->matchSingle('#foo_day')->getNode()->nextSibling->firstChild->nodeValue, '/', '->render() renders 3 selects with a default / as a separator');
$t->is($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '/', '->render() renders 3 selects with a default / as a separator');

$w->setOption('separator', '#');
$dom->loadHTML($w->render('foo', '2005-10-15'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#foo_day')->getNode()->nextSibling->firstChild->nodeValue, '#', '__construct() can change the default separator');
$t->is($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '#', '__construct() can change the default separator');

// days / months / years options
$t->diag('days / months / years options');
$w->setOption('years', array(1998 => 1998, 1999 => 1999, 2000 => 2000, 2001 => 2001));
$w->setOption('months', array(1 => 1, 2 => 2, 3 => 3));
$w->setOption('days', array(1 => 1, 2 => 2));
$dom->loadHTML($w->render('foo', '2005-10-15'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo_year option')->getNodes()), 4, '__construct() can change the default array used for years');
$t->is(count($css->matchAll('#foo_month option')->getNodes()), 3, '__construct() can change the default array used for months');
$t->is(count($css->matchAll('#foo_day option')->getNodes()), 2, '__construct() can change the default array used for days');
