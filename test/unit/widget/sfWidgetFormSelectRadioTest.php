<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');
$w = new sfWidgetFormSelectRadio(array('choices' => array('foo' => 'bar', 'foobar' => 'foo'), 'separator' => ''));
$output = '<ul class="radio_list"><li><input name="foo" type="radio" value="foo" id="foo_foo" />&nbsp;<label for="foo_foo">bar</label></li>'.
'<li><input name="foo" type="radio" value="foobar" id="foo_foobar" checked="checked" />&nbsp;<label for="foo_foobar">foo</label></li></ul>';
$t->is($w->render('foo', 'foobar'), $output, '->render() renders a select tag with the value selected');

// choices as a callable
$t->diag('choices as a callable');

function choice_callable()
{
  return array(1, 2, 3);
}
$w = new sfWidgetFormSelectRadio(array('choices' => new sfCallable('choice_callable')));
$dom->loadHTML($w->render('foo'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('input[type="radio"]')->getNodes()), 3, '->render() accepts a sfCallable as a choices option');
