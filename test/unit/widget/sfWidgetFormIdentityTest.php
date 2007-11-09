<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$w = new sfWidgetFormIdentity();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo', 'bar'), 'bar', '->render() renders the widget as HTML');
