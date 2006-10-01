<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../bootstrap.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Number'));

$t = new lime_test(2, new lime_output_color());

// format_number()
$t->diag('format_number()');
$t->is(format_number(10012.1, 'en'), '10,012.1', 'format_number() takes a number as its first argument');
//$t->is(format_number(10012.1, 'fr'), '10.012,1', 'format_number() takes a culture as its second argument');

$t->todo('format_number() takes the current user culture if no second argument is given');

// format_currency()
