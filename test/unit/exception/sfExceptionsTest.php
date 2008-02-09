<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(16, new lime_output_color());

foreach (array(
  'cache', 'configuration', 'controller', 'database', 
  'error404', 'factory', 'file', 'filter', 'forward', 'initialization', 'parse', 'render', 'security',
  'stop', 'storage', 'view'
) as $class)
{
  $class = sprintf('sf%sException', ucfirst($class));
  $e = new $class();
  $t->ok($e instanceof sfException, sprintf('"%s" inherits from sfException', $class));
}
