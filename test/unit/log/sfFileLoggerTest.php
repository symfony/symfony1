<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

require_once(dirname(__FILE__).'/../../../lib/util/sfToolkit.class.php');
$file = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'sf_log_file.txt';
if (file_exists($file))
{
  unlink($file);
}

$dispatcher = new sfEventDispatcher();

// ->initialize()
$t->diag('->initialize()');
$logger = sfLogger::newInstance('sfFileLogger');
try
{
  $logger->initialize($dispatcher);
  $t->fail('->initialize() parameters must contains a "file" parameter');
}
catch (sfConfigurationException $e)
{
  $t->pass('->initialize() parameters must contains a "file" parameter');
}

// ->log()
$t->diag('->log()');
$logger->initialize($dispatcher, array('file' => $file));
$logger->log('foo');
$lines = explode("\n", file_get_contents($file));
$t->like($lines[0], '/foo/', '->log() logs a message to the file');
$logger->log('bar');
$lines = explode("\n", file_get_contents($file));
$t->like($lines[1], '/bar/', '->log() logs a message to the file');

// ->shutdown()
$t->diag('->shutdown()');
$logger->shutdown();

unlink($file);
