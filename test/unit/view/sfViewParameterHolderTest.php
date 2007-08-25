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

$t = new lime_test(6, new lime_output_color());

class myUser
{
  public function getAttributeHolder()
  {
    return new sfParameterHolder();
  }
}

class myRequest
{
  public function getParameterHolder()
  {
    return new sfParameterHolder();
  }
}

$context = sfContext::getInstance(array(
  'user'    => 'myUser',
  'request' => 'myRequest',
));

// ->initialize()
$t->diag('->initialize()');
$p = new sfViewParameterHolder();
$p->initialize($context);
$t->is($p->get('sf_user'), $context->user, '->initialize() add some symfony shortcuts as parameters');
$t->is($p->get('sf_request'), $context->request, '->initialize() add some symfony shortcuts as parameters');
$t->is($p->get('sf_response'), $context->response, '->initialize() add some symfony shortcuts as parameters');

$p->initialize($context, array('foo' => 'bar'));
$t->is($p->get('foo'), 'bar', '->initialize() takes an array of default parameters as its second argument');

// ->toArray()
$t->diag('->toArray()');
$p->initialize($context, array('foo' => 'bar'));
$a = $p->toArray();
$t->is($a['foo'], 'bar', '->toArray() returns an array representation of the parameter holder');

// ->serialize() / ->unserialize()
$t->diag('->serialize() / ->unserialize()');
$p->initialize($context, array('foo' => 'bar'));
$unserialized = unserialize(serialize($p));
$t->is($p->toArray(), $unserialized->toArray(), 'sfViewParameterHolder implements the Serializable interface');
