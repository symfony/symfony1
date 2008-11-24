<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

//lazy constant, cause it is so often used in this test
define("DS", DIRECTORY_SEPARATOR);

class myFilesystem extends sfFilesystem
{
  public function calculateRelativeDir($from, $to)
  {
    return parent::calculateRelativeDir($from, $to);
  }
}

$t = new lime_test(6, new lime_output_color());

$dispatcher = new sfEventDispatcher();
$filesystem = new myFilesystem($dispatcher, null);

$t->diag('sfFilesystem calculates relative pathes');
$common = DS.'tmp'.DS.'sfproject'.DS;
$source = $common.'web'.DS.'myplugin';
$target = $common.'plugins'.DS.'myplugin'.DS.'web';
$t->is($filesystem->calculateRelativeDir($source, $target), '..'.DS.'plugins'.DS.'myplugin'.DS.'web', '->calculateRelativeDir() correctly calculates the relative path');

$source = $common.'web'.DS.'myplugin';
$target = $common.'webplugins'.DS.'myplugin'.DS.'web';
$t->is($filesystem->calculateRelativeDir($source, $target), '..'.DS.'webplugins'.DS.'myplugin'.DS.'web', '->calculateRelativeDir() correctly calculates the relative path for dirs that share chars');

$source = $common.'web'.DS.'myplugin';
$target = $common.'web'.DS.'otherplugin'.DS.'sub';
$t->is($filesystem->calculateRelativeDir($source, $target), 'otherplugin'.DS.'sub', '->calculateRelativeDir() works without going up one dir');

$source = 'c:\sfproject\web\myplugin';
$target = 'd:\symfony\plugins\myplugin\web';
$t->is($filesystem->calculateRelativeDir($source, $target), 'd:\symfony\plugins\myplugin\web', '->calculateRelativeDir() returns absolute path when no relative path possible');

$source = $common.'web'.DS.'myplugin';
$target = $common.'web'.DS.'myotherplugin'.DS.'sub';
$t->is($filesystem->calculateRelativeDir($source, $target), 'myotherplugin'.DS.'sub', '->calculateRelativeDir() correctly calculates the relative path for dirs that share chars');

$source = $common.'web'.DS.'myplugin';
$target = $common.'web'.DS.'motherplugin'.DS.'sub';
$t->is($filesystem->calculateRelativeDir($source, $target), 'motherplugin'.DS.'sub', '->calculateRelativeDir() correctly calculates the relative path for dirs that share chars');
