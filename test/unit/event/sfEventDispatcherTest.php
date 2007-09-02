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

$dispatcher = new sfEventDispatcher();

// ->connect()

// ->hasListeners()
$t->is($dispatcher->hasListeners('foo'), false, '->hasListeners() returns true if the event name has some listeners');
$dispatcher->connect('foo', 'listenToFoo');
$t->is($dispatcher->hasListeners('foo'), true, '->hasListeners() returns true if the event name has some listeners');
$dispatcher->disconnect('foo', 'listenToFoo');
$t->is($dispatcher->hasListeners('foo'), false, '->disconnect() removes a listener');

function listenToFoo(sfEvent $event)
{
  return $event;
}
