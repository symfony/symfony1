<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

// ->getInstance()
$t->diag('->getInstance()');
$t->isa_ok(sfConfigCache::getInstance(), 'sfConfigCache', '::getInstance() returns a sfConfigCache instance');
$t->is(sfConfigCache::getInstance(), sfConfigCache::getInstance(), '::getInstance() is a singleton');
