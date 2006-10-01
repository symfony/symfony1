<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../lib/vendor/lime/lime.php');

$h = new lime_harness(new lime_output_color());

$h->base_dir = dirname(__FILE__);

// cache autoload files
require_once(dirname(__FILE__).'/unit/bootstrap.php');
testAutoloader::initialize(true);

// unit tests
$h->register_glob($h->base_dir.'/unit/*/*Test.php');

// functional tests
$h->register_glob($h->base_dir.'/functional/*Test.php');

// other tests
$h->register_glob($h->base_dir.'/other/*Test.php');

$h->run();

testAutoloader::removeCache();
