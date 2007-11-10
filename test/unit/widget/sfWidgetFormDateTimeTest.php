<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(40, new lime_output_color());

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

$w = new sfWidgetFormDateTime(array('with_time' => true, 'time' => array('with_seconds' => true)));

// ->render()
$t->diag('->render()');

foreach (array(
  '2005-10-15 12:30:35' => array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 12, 'minute' => 30, 'second' => 35),
  time() => array('year' => date('Y'), 'month' => date('m'), 'day' => date('d'), 'hour' => date('G'), 'minute' => date('i'), 'second' => date('s')),
  'tomorrow 12:30:35' => array('year' => date('Y', time() + 86400), 'month' => date('m', time() + 86400), 'day' => date('d', time() + 86400), 'hour' => 12, 'minute' => 30, 'second' => 35),
) as $date => $values)
{
  $dom->loadHTML($w->render('foo', $date));
  $css = new sfDomCssSelector($dom);

  // selected date / time
  $t->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
  $t->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
  $t->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');
  $t->is($css->matchSingle('#foo_hour option[value="'.$values['hour'].'"][selected="selected"]')->getValue(), $values['hour'], '->render() renders a select tag for the hour');
  $t->is($css->matchSingle('#foo_minute option[value="'.$values['minute'].'"][selected="selected"]')->getValue(), $values['minute'], '->render() renders a select tag for the minute');
  $t->is($css->matchSingle('#foo_second option[value="'.$values['second'].'"][selected="selected"]')->getValue(), $values['second'], '->render() renders a select tag for the second');
}

$values = array('year' => 2005, 'month' => 10, 'day' => 15, 'hour' => 12, 'minute' => 30, 'second' => 35);
$dom->loadHTML($w->render('foo', $values));
$css = new sfDomCssSelector($dom);

// selected date / time
$t->is($css->matchSingle('#foo_year option[value="'.$values['year'].'"][selected="selected"]')->getValue(), $values['year'], '->render() renders a select tag for the year');
$t->is($css->matchSingle('#foo_month option[value="'.$values['month'].'"][selected="selected"]')->getValue(), $values['month'], '->render() renders a select tag for the month');
$t->is($css->matchSingle('#foo_day option[value="'.$values['day'].'"][selected="selected"]')->getValue(), $values['day'], '->render() renders a select tag for the day');
$t->is($css->matchSingle('#foo_hour option[value="'.$values['hour'].'"][selected="selected"]')->getValue(), $values['hour'], '->render() renders a select tag for the hour');
$t->is($css->matchSingle('#foo_minute option[value="'.$values['minute'].'"][selected="selected"]')->getValue(), $values['minute'], '->render() renders a select tag for the minute');
$t->is($css->matchSingle('#foo_second option[value="'.$values['second'].'"][selected="selected"]')->getValue(), $values['second'], '->render() renders a select tag for the second');

$dom->loadHTML($w->render('foo', '2005-10-15 12:30:35'));
$css = new sfDomCssSelector($dom);

// number of options in each select
$t->is(count($css->matchAll('#foo_year option')->getNodes()), 11, '->render() renders a select tag for the 10 years around the current one');
$t->is(count($css->matchAll('#foo_month option')->getNodes()), 12, '->render() renders a select tag for the 12 months in a year');
$t->is(count($css->matchAll('#foo_day option')->getNodes()), 31, '->render() renders a select tag for the 31 days in a month');
$t->is(count($css->matchAll('#foo_hour option')->getNodes()), 24, '->render() renders a select tag for the 24 hours in a day');
$t->is(count($css->matchAll('#foo_minute option')->getNodes()), 60, '->render() renders a select tag for the 60 minutes in an hour');
$t->is(count($css->matchAll('#foo_second option')->getNodes()), 60, '->render() renders a select tag for the 60 seconds in a minute');

// date and time separator option
$t->diag('date and time separator option');
$t->is($css->matchSingle('#foo_day')->getNode()->nextSibling->firstChild->nodeValue, '/', '->render() renders 3 selects with a default / as a separator');
$t->is($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '/', '->render() renders 3 selects with a default / as a separator');
$t->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a separator');
$t->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a separator');

$t->diag('change date and time separator option');
$w->setOption('date', array('separator' => '-'));
$w->setOption('time', array('separator' => '!', 'with_seconds' => true));
$dom->loadHTML($w->render('foo', '2005-10-15 12:30:35'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#foo_day')->getNode()->nextSibling->firstChild->nodeValue, '-', '__construct() can change the default separator');
$t->is($css->matchSingle('#foo_month')->getNode()->nextSibling->nodeValue, '-', '__construct() can change the default separator');
$t->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->nodeValue, '!', '__construct() can change the default separator');
$t->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, '!', '__construct() can change the default separator');

// with_time option
$t->diag('with_time option');

$w = new sfWidgetFormDateTime(array('with_time' => false));
$dom->loadHTML($w->render('foo', '2005-10-15 12:30:35'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo_hour')->getNodes()), 0, '->render() does not render the time if the with_time option is disabled');

// date and time options as array
$t->diag('date and time options as array');
$w = new sfWidgetFormDateTime(array('date' => 'a string'));
try
{
  $w->render('foo');
  $t->fail('__construct() throws a sfException if the date/time options is not an array');
}
catch (sfException $e)
{
  $t->pass('__construct() throws a sfException if the date/time options is not an array');
}
