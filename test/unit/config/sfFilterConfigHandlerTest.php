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

$handler = new sfFilterConfigHandler();
$handler->initialize();

$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfFilterConfigHandler'.DIRECTORY_SEPARATOR;

$t->diag('parse errors');
$files = array(
  $dir.'no_class.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('filters.yml must have a "class" section for each filter entry');
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/with missing class key/', 'filters.yml must have a "class" section for each filter entry');
}

$files = array(
  $dir.'default_filters.yml',
  $dir.'filters.yml',
);

$data = $handler->execute($files);
$data = preg_replace('#date\: \d+/\d+/\d+ \d+\:\d+\:\d+#', '', $data);

$t->is($data, str_replace("\r\n", "\n", file_get_contents($dir.'result.php')), 'core filters.yml can be overriden');
