<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(40, new lime_output_color());

foreach (array(
  'action', 'actionStop', 'autoload', 'cache', 'configuration', 'context', 'controller', 'database', 
  'error404', 'factory', 'file', 'filter', 'forward', 'initialization', 'parse', 'render', 'security',
  'storage', 'validator', 'view'
) as $class)
{
  $class = sprintf('sf%sException', ucfirst($class));
  $e = new $class();
  $t->is($e->getName(), $class, sprintf('"%s" exception name is "%s"', $class, $class));
  $t->is(get_parent_class($e), 'sfException', sprintf('"%s" inherits from sfException', $class));
}
