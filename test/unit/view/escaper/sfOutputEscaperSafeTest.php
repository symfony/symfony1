<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../../../lib/vendor/lime/lime.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperSafe.class.php');

$t = new lime_test(1, new lime_output_color());

// ->getValue()
$t->diag('->getValue()');
$safe = new sfOutputEscaperSafe('foo');
$t->is($safe->getValue(), 'foo', '->getValue() returns the embedded value');
