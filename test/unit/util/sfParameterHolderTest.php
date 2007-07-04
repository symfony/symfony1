<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(54, new lime_output_color());

// ->clear()
$t->diag('->clear()');
$ph = new sfParameterHolder();
$ph->clear();
$t->is($ph->getAll(), null, '->clear() clears all parameters');

$ph->set('foo', 'bar');
$ph->clear();
$t->is($ph->getAll(), null, '->clear() clears all parameters');

// ->get()
$t->diag('->get()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$t->is($ph->get('foo'), 'bar', '->get() returns the parameter value for the given key');
$t->is($ph->get('bar'), null, '->get() returns null if the key does not exist');

$ph = new sfParameterHolder();
$t->is('default_value', $ph->get('foo1', 'default_value'), '->get() takes the default value as its second argument');

$ph = new sfParameterHolder();
$ph->set('myfoo', 'bar', 'symfony/mynamespace');
$t->is('bar', $ph->get('myfoo', null, 'symfony/mynamespace'), '->get() takes an optional namespace as its third argument');
$t->is(null, $ph->get('myfoo'), '->get() can have the same key for several namespaces');

$ph = new sfParameterHolder();
$ph->add(array('foo' => array(
  'bar' => array(
    'baz' => 'foo bar',
  ),
  'bars' => array('foo', 'bar'),
)));
$t->is($ph->get('foo[bar][baz]'), 'foo bar', '->get() can take a multi-array key');
$t->is($ph->get('foo[bars][1]'), 'bar', '->get() can take a multi-array key');
$t->is($ph->get('foo[bars][2]'), null, '->get() returns null if the key does not exist');
$t->is($ph->get('foo[bars][]'), array('foo', 'bar'), '->get() returns an array');
$t->is($ph->get('foo[bars][]'), $ph->get('foo[bars]'), '->get() returns an array even if you omit the []');

// ->getNames()
$t->diag('->getNames()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('yourfoo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');

$t->is($ph->getNames(), array('foo', 'yourfoo'), '->getNames() returns all key names for the default namespace');
$t->is($ph->getNames('symfony/mynamespace'), array('myfoo'), '->getNames() takes a namepace as its first argument');

// ->getNamespaces()
$t->diag('->getNamespaces()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('yourfoo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');

$t->is($ph->getNamespaces(), array($ph->getDefaultNamespace(), 'symfony/mynamespace'), '->getNamespaces() returns all non empty namespaces');

// ->getAll()
$t->diag('->getAll()');
$parameters = array('foo' => 'bar', 'myfoo' => 'bar');
$ph = new sfParameterHolder();
$ph->add($parameters);
$ph->set('myfoo', 'bar', 'symfony/mynamespace');
$t->is($ph->getAll(), $parameters, '->getAll() returns all parameters from the default namespace');

// ->has()
$t->diag('->has()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');
$t->is($ph->has('foo'), true, '->has() returns true if the key exists');
$t->is($ph->has('bar'), false, '->has() returns false if the key does not exist');
$t->is($ph->has('myfoo'), false, '->has() returns false if the key exists but in another namespace');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->has() returns true if the key exists in the namespace given as its second argument');

$ph = new sfParameterHolder();
$ph->add(array('foo' => array(
  'bar' => array(
    'baz' => 'foo bar',
  ),
  'bars' => array('foo', 'bar'),
)));
$t->is($ph->has('foo[bar][baz]'), true, '->has() can takes a multi-array key');
$t->is($ph->get('foo[bars][1]'), true, '->has() can takes a multi-array key');
$t->is($ph->get('foo[bars][2]'), false, '->has() returns null is the key does not exist');
$t->is($ph->has('foo[bars][]'), true, '->has() returns true if an array exists');
$t->is($ph->get('foo[bars][]'), $ph->has('foo[bars]'), '->has() returns true for an array even if you omit the []');

// ->hasNamespace()
$t->diag('->hasNamespace()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');
$t->is($ph->hasNamespace($ph->getDefaultNamespace()), true, '->hasNamespace() returns true for the default namespace');
$t->is($ph->hasNamespace('symfony/mynamespace'), true, '->hasNamespace() returns true if the namespace exists');
$t->is($ph->hasNamespace('symfony/nonexistant'), false, '->hasNamespace() returns false if the namespace does not exist');

// ->remove()
$t->diag('->remove()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('myfoo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');

$ph->remove('foo');
$t->is($ph->has('foo'), false, '->remove() removes the key from parameters');

$ph->remove('myfoo');
$t->is($ph->has('myfoo'), false, '->remove() removes the key from parameters');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->remove() removes the key from parameters for a given namespace');

$ph->remove('myfoo', 'symfony/mynamespace');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), false, '->remove() takes a namespace as its second argument');

$t->is($ph->getAll(), null, '->remove() removes the key from parameters');

// ->removeNamespace()
$t->diag('->removeNamespace()');
$ph = new sfParameterHolder();
$ph->set('foo', 'bar');
$ph->set('myfoo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');

$ph->removeNamespace($ph->getDefaultNamespace());
$t->is($ph->has('foo'), false, '->removeNamespace() removes all keys and values from a namespace');
$t->is($ph->has('myfoo'), false, '->removeNamespace() removes all keys and values from a namespace');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->removeNamespace() does not remove keys in other namepaces');

$ph->set('foo', 'bar');
$ph->set('myfoo', 'bar');
$ph->set('myfoo', 'bar', 'symfony/mynamespace');

$ph->removeNamespace();
$t->is($ph->has('foo'), false, '->removeNamespace() removes all keys and values from the default namespace by default');
$t->is($ph->has('myfoo'), false, '->removeNamespace() removes all keys and values from the default namespace by default');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), true, '->removeNamespace() does not remove keys in other namepaces');

$ph->removeNamespace('symfony/mynamespace');
$t->is($ph->has('myfoo', 'symfony/mynamespace'), false, '->removeNamespace() takes a namespace as its first parameter');

$t->is(null, $ph->getAll(), '->removeNamespace() removes all the keys from parameters');

// ->set()
$t->diag('->set()');
$foo = 'bar';

$ph = new sfParameterHolder();
$ph->set('foo', $foo);
$t->is($ph->get('foo'), $foo, '->set() sets the value for a key');

$foo = 'foo';
$t->is($ph->get('foo'), 'bar', '->set() sets the value for a key, not a reference');

$ph->set('myfoo', 'bar', 'symfony/mynamespace');
$t->is($ph->get('myfoo', null, 'symfony/mynamespace'), 'bar', '->set() takes a namespace as its third parameter');

// ->setByRef()
$t->diag('->setByRef()');
$foo = 'bar';

$ph = new sfParameterHolder();
$ph->setByRef('foo', $foo);
$t->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key');

$foo = 'foo';
$t->is($ph->get('foo'), $foo, '->setByRef() sets the value for a key as a reference');

$myfoo = 'bar';
$ph->setByRef('myfoo', $myfoo, 'symfony/mynamespace');
$t->is($ph->get('myfoo', null, 'symfony/mynamespace'), $myfoo, '->setByRef() takes a namespace as its third parameter');

// ->add()
$t->diag('->add()');
$foo = 'bar';
$parameters = array('foo' => $foo, 'bar' => 'bar');
$myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

$ph = new sfParameterHolder();
$ph->add($parameters);
$ph->add($myparameters, 'symfony/mynamespace');

$t->is($ph->getAll(), $parameters, '->add() adds an array of parameters');
$t->is($ph->getAll('symfony/mynamespace'), $myparameters, '->add() takes a namespace as its second argument');

$foo = 'mybar';
$t->is($ph->getAll(), $parameters, '->add() adds an array of parameters, not a reference');

// ->addByRef()
$t->diag('->addByRef()');
$foo = 'bar';
$parameters = array('foo' => &$foo, 'bar' => 'bar');
$myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

$ph = new sfParameterHolder();
$ph->addByRef($parameters);
$ph->addByRef($myparameters, 'symfony/mynamespace');

$t->is($parameters, $ph->getAll(), '->add() adds an array of parameters');
$t->is($myparameters, $ph->getAll('symfony/mynamespace'), '->add() takes a namespace as its second argument');

$foo = 'mybar';
$t->is($parameters, $ph->getAll(), '->add() adds a reference of an array of parameters');

// ->serialize() ->unserialize()
$t->diag('->serialize() ->unserialize()');
$t->ok($ph == unserialize(serialize($ph)), 'sfParameterHolder implements the Serializable interface');
