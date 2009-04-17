<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

// ->__construct()
$t->diag('->__construct()');

try
{
  $collection = new sfObjectRouteCollection(array('name' => 'test_collection'));
  $t->fail('->__construct() throws an exception if no "model" option is provided');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->__construct() throws an exception if no "model" option is provided');
}

$collection = new sfObjectRouteCollection(array('name' => 'test_collection', 'model' => 'TestModel'));
$options = $collection->getOptions();
$t->is($options['column'], 'id', '->__construct() defaults "column" option to "id"');
$t->is_deeply($options['requirements'], array('id' => '\d+'), '->__construct() defaults "requirements" for column to "\d+"');

$collection = new sfObjectRouteCollection(array('name' => 'test_collection', 'model' => 'TestModel', 'column' => 'slug'));
$options = $collection->getOptions();
$t->is_deeply($options['requirements'], array('slug' => null), '->__construct() does not set a default requirement for custom columns');
