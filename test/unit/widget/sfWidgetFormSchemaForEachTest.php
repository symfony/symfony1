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

$w1 = new sfWidgetFormInput();
$w = new sfWidgetFormSchema(array('w1' => $w1));

// __construct()
$t->diag('__construct()');
$wf = new sfWidgetFormSchemaForEach('article[%s]', $w, 2);
$wc1 = clone $w;
$wc1->setNameFormat('article[0][%s]');
$wc2 = clone $w;
$wc2->setNameFormat('article[1][%s]');
$t->is($wf->getFields(), array($wc1, $wc2), '__construct() takes a sfWidgetFormSchema as its second argument');
