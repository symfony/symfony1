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

$w = new sfWidgetFormTextarea();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo', 'bar'), '<textarea name="foo" cols="30" rows="4" id="foo">bar</textarea>', '->render() renders the widget as HTML');
$t->is($w->render('foo', '<bar>'), '<textarea name="foo" cols="30" rows="4" id="foo">&lt;bar&gt;</textarea>', '->render() escapes the content');
$t->is($w->render('foo', '&lt;bar&gt;'), '<textarea name="foo" cols="30" rows="4" id="foo">&lt;bar&gt;</textarea>', '->render() does not double escape content');
