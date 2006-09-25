<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/unit/bootstrap.php');

sfConfig::set('sf_symfony_lib_dir', realpath(dirname(__FILE__).'/../../../lib'));

$t = new lime_test(1, new lime_output_color());

// prefix
$handler = new sfDefineEnvironmentConfigHandler();
$handler->initialize(array('prefix' => 'sf_'));

$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;

$files = array(
  $dir.'prefix_default.yml',
  $dir.'prefix_all.yml',
);

sfConfig::set('sf_environment', 'prod');

$data = $handler->execute($files);
$data = preg_replace('#date\: \d+/\d+/\d+ \d+\:\d+\:\d+#', '', $data);

$t->is($data, str_replace("\r\n", "\n", file_get_contents($dir.'prefix_result.php')));
