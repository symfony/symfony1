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

// ->matchesParameters()
$t->diag('->matchesParameters()');

$route = new sfRequestRoute('/', array(), array('sf_method' => array('get', 'head')));
$t->ok($route->matchesParameters(array('sf_method' => 'get')), '->matchesParameters() matches the "sf_method" parameter');
