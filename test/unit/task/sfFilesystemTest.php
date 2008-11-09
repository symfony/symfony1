<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class myFilesystem extends sfFilesystem
{
  public function calculateRelativeDir($from, $to)
  {
    return parent::calculateRelativeDir($from, $to);
  }
}

$t = new lime_test(3, new lime_output_color());

$dispatcher = new sfEventDispatcher();
$filesystem = new myFilesystem($dispatcher, null);

$t->diag('sfFilesystem calculates relative pathes');
$common = DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sfproject'.DIRECTORY_SEPARATOR;
$source = $common.'web'.DIRECTORY_SEPARATOR.'myplugin';
$target = $common.'plugins'.DIRECTORY_SEPARATOR.'myplugin'.DIRECTORY_SEPARATOR.'web';
$t->is($filesystem->calculateRelativeDir($source, $target), '..'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'myplugin'.DIRECTORY_SEPARATOR.'web', '->calculateRelativeDir() correctly calculates the relative path');

$source = $common.'web'.DIRECTORY_SEPARATOR.'myplugin';
$target = $common.'web'.DIRECTORY_SEPARATOR.'otherplugin'.DIRECTORY_SEPARATOR.'sub';
$t->is($filesystem->calculateRelativeDir($source, $target), 'otherplugin'.DIRECTORY_SEPARATOR.'sub', '->calculateRelativeDir() works without going up one dir');

$source = 'c:\sfproject\web\myplugin';
$target = 'd:\symfony\plugins\myplugin\web';
$t->is($filesystem->calculateRelativeDir($source, $target), 'd:\symfony\plugins\myplugin\web', '->calculateRelativeDir() returns absolute path when no relative path possible');