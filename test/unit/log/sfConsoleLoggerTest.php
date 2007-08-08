<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$logger = sfLogger::newInstance('sfConsoleLogger');
ob_start();
$logger->log('foo');
$t->is(ob_get_clean(), 'foo', 'sfConsoleLogger logs messages to the console');
