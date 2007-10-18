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

$v1 = new sfValidatorString(array('max_length' => 3));

// __construct()
$t->diag('__construct()');
$v = new sfValidatorSchemaForEach($v1, 3);
$t->is($v->getFields(), array($v1, $v1, $v1), '->__construct() takes a sfValidator object as its first argument');

$v = new sfValidatorSchemaForEach($v1, 5);
$t->is($v->getFields(), array($v1, $v1, $v1, $v1, $v1), '->__construct() takes a number of times to duplicate the validator');
