<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(4, new lime_output_color());

class myRequest
{
  public function getParameterHolder()
  {
    return new sfParameterHolder();
  }
}

class FilterParameters
{
  static public $context = null;

  static function filter($event, $parameters)
  {
    $parameters['sf_request'] = self::$context->request;

    return $parameters;
  }
}

$context = sfContext::getInstance(array('request' => 'myRequest'));
$dispatcher = $context->dispatcher;

FilterParameters::$context = $context;
$dispatcher->connect('template.filter_parameters', array('FilterParameters', 'filter'));

// ->initialize()
$t->diag('->initialize()');
$p = new sfViewParameterHolder();
$p->initialize($dispatcher, array('foo' => 'bar'));
$t->is($p->get('foo'), 'bar', '->initialize() takes an array of default parameters as its second argument');

// ->toArray()
$t->diag('->toArray()');
$p->initialize($dispatcher, array('foo' => 'bar'));
$a = $p->toArray();
$t->is($a['foo'], 'bar', '->toArray() returns an array representation of the parameter holder');

// ->serialize() / ->unserialize()
$t->diag('->serialize() / ->unserialize()');
$p->initialize($dispatcher, array('foo' => 'bar'));
$unserialized = unserialize(serialize($p));
$t->is($p->toArray(), $unserialized->toArray(), 'sfViewParameterHolder implements the Serializable interface');

// template.filter_parameters
$p = new sfViewParameterHolder();
$p->initialize($dispatcher);
$t->is($p->get('sf_request'), $context->request, '->initialize() add some symfony shortcuts as parameters');
