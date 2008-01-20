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

$t = new lime_test(27, new lime_output_color());

define('ESC_ENTITIES', 'esc_entities');
function esc_entities($value)
{
  return "-ESCAPED-$value-ESCAPED-";
}

define('ESC_RAW', 'esc_raw');
function esc_raw($value)
{
  return $value;
}

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

$context = sfContext::getInstance(array(
  'request' => 'myRequest',
));
$dispatcher = $context->dispatcher;

FilterParameters::$context = $context;
$dispatcher->connect('template.filter_parameters', array('FilterParameters', 'filter'));

// ->initialize()
$t->diag('->initialize()');
$p = new sfEscapedViewParameterHolder();
$p->initialize($dispatcher);
$t->is($p->get('sf_user'), $context->user, '->initialize() add some symfony shortcuts as parameters');
$t->is($p->get('sf_request'), $context->request, '->initialize() add some symfony shortcuts as parameters');
$t->is($p->get('sf_response'), $context->response, '->initialize() add some symfony shortcuts as parameters');

$p->initialize($dispatcher, array('foo' => 'bar'));
$t->is($p->get('foo'), 'bar', '->initialize() takes an array of default parameters as its second argument');

$p->initialize($dispatcher, array(), array('escaping_strategy' => 'on', 'escaping_method' => 'ESC_RAW'));
$t->is($p->getEscaping(), 'on', '->initialize() takes an array of options as its third argument');
$t->is($p->getEscapingMethod(), ESC_RAW, '->initialize() takes an array of options as its third argument');

// ->isEscaped()
$t->diag('->isEscaped()');
$t->is($p->isEscaped(), true, '->isEscaped() always returns true');

// ->getEscaping() ->setEscaping()
$t->diag('->getEscaping() ->setEscaping()');
$p->initialize($dispatcher);
$p->setEscaping('on');
$t->is($p->getEscaping(), 'on', '->setEscaping() changes the escaping strategy');

// ->getEscapingMethod() ->setEscapingMethod()
$t->diag('->getEscapingMethod() ->setEscapingMethod()');
$p->setEscapingMethod('ESC_RAW');
$t->is($p->getEscapingMethod(), ESC_RAW, '->setEscapingMethod() changes the escaping method');

$p->setEscapingMethod('');
$t->is($p->getEscapingMethod(), '', '->getEscapingMethod() returns an empty value if the method is empty');

try
{
  $p->setEscapingMethod('nonexistant');
  $p->getEscapingMethod();
  $t->fail('->getEscapingMethod() throws an InvalidArgumentException if the escaping method does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->getEscapingMethod() throws an InvalidArgumentException if the escaping method does not exist');
}

// ->toArray()
$t->diag('->toArray()');
$p->initialize($dispatcher, array('foo' => 'bar'));
$a = $p->toArray();
$t->is($a['foo'], 'bar', '->toArray() returns an array representation of the parameter holder');

// escaping strategies
$p = new sfEscapedViewParameterHolder();
$p->initialize(new sfEventDispatcher(), array('foo' => 'bar'));

$t->diag('Escaping strategy to on');
$p->setEscaping('on');
$values = $p->toArray();
$t->is(count($values), 1, '->toArray() knows about the "on" strategy');
$t->is(count($values['sf_data']), 1, '->toArray() knows about the "on" strategy');
$t->is($values['sf_data']['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "on" strategy');

try
{
  $p->setEscaping('null');
  $p->toArray();
  $t->fail('->toArray() throws an InvalidArgumentException if the escaping strategy does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->toArray() throws an InvalidArgumentException if the escaping strategy does not exist');
}

$t->diag('Escaping strategy to bc');
$p->setEscaping('bc');
$values = $p->toArray();
$t->is(count($values), 2, '->toArray() knows about the "bc" strategy');
$t->is(count($values['sf_data']), 1, '->toArray() knows about the "bc" strategy');
$t->is($values['foo'], 'bar', '->toArray() knows about the "bc" strategy');
$t->is($values['sf_data']['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "bc" strategy');

$t->diag('Escaping strategy to both');
$p->setEscaping('both');
$values = $p->toArray();
$t->is(count($values), 2, '->toArray() knows about the "both" strategy');
$t->is(count($values['sf_data']), 1, '->toArray() knows about the "both" strategy');
$t->is($values['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "both" strategy');
$t->is($values['sf_data']['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "both" strategy');

$t->diag('Escaping strategy to off');
$p->setEscaping('off');
$values = $p->toArray();
$t->is(count($values), 1, '->toArray() knows about the "off" strategy');
$t->is($values['foo'], 'bar', '->toArray() knows about the "off" strategy');

// ->serialize() / ->unserialize()
$t->diag('->serialize() / ->unserialize()');
$p->initialize($dispatcher, array('foo' => 'bar'));
$unserialized = unserialize(serialize($p));
$t->is($p->toArray(), $unserialized->toArray(), 'sfEscapedViewParameterHolder implements the Serializable interface');
