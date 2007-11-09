<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(17, new lime_output_color());

$w = new sfWidgetFormTime(array('with_seconds' => true));

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');

foreach (array(
  '12:30:35',
  mktime(12, 30, 35, 15, 10, 2005),
) as $date)
{
  $dom->loadHTML($w->render('foo', $date));
  $css = new sfDomCssSelector($dom);

  // selected date
  $t->is($css->matchSingle('#foo_hour option[value="12"][selected="selected"]')->getValue(), 12, '->render() renders a select tag for the hour');
  $t->is($css->matchSingle('#foo_minute option[value="30"][selected="selected"]')->getValue(), 30, '->render() renders a select tag for the minute');
  $t->is($css->matchSingle('#foo_second option[value="35"][selected="selected"]')->getValue(), 35, '->render() renders a select tag for the second');
}

$dom->loadHTML($w->render('foo', '12:30:35'));
$css = new sfDomCssSelector($dom);

// number of options in each select
$t->is(count($css->matchAll('#foo_hour option')->getNodes()), 24, '->render() renders a select tag for the 24 hours in a day');
$t->is(count($css->matchAll('#foo_minute option')->getNodes()), 60, '->render() renders a select tag for the 60 minutes in an hour');
$t->is(count($css->matchAll('#foo_second option')->getNodes()), 60, '->render() renders a select tag for the 60 seconds in a minute');

// separator option
$t->diag('separator option');
$t->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->firstChild->nodeValue, ':', '->render() renders 3 selects with a default : as a separator');
$t->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, ':', '->render() renders 3 selects with a default : as a separator');

$w->setOption('separator', '#');
$dom->loadHTML($w->render('foo', '12:30:35'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#foo_hour')->getNode()->nextSibling->firstChild->nodeValue, '#', '__construct() can change the default separator');
$t->is($css->matchSingle('#foo_minute')->getNode()->nextSibling->nodeValue, '#', '__construct() can change the default separator');

// hours / minutes / seconds options
$t->diag('hours / minutes / seconds options');
$w->setOption('hours', array(1 => 1, 2 => 2, 3 => 3, 4 => 4));
$w->setOption('minutes', array(1 => 1, 2 => 2));
$w->setOption('seconds', array(15 => 15, 30 => 30, 45 => 45));
$dom->loadHTML($w->render('foo', '12:30:35'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo_hour option')->getNodes()), 4, '__construct() can change the default array used for hours');
$t->is(count($css->matchAll('#foo_minute option')->getNodes()), 2, '__construct() can change the default array used for minutes');
$t->is(count($css->matchAll('#foo_second option')->getNodes()), 3, '__construct() can change the default array used for seconds');

// with_seconds option
$t->diag('with_seconds option');
$w->setOption('with_seconds', false);
$dom->loadHTML($w->render('foo', '12:30:35'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo_second option')), 0, '__construct() can enable or disable the seconds select box with the with_seconds option');
