<?php

class sfParameterHolderTest extends UnitTestCase
{
  public function test_clear()
  {
    $ph = new sfParameterHolder();
    $ph->clear();
    $this->assertEqual(null, $ph->getAll());

    $ph->set('foo', 'bar');
    $ph->clear();
    $this->assertEqual(null, $ph->getAll());
  }

  public function test_get()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $this->assertEqual('bar', $ph->get('foo'));

    $ph = new sfParameterHolder();
    $this->assertEqual('default_value', $ph->get('foo1', 'default_value'));

    $ph = new sfParameterHolder();
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');
    $this->assertEqual('bar', $ph->get('myfoo', null, 'symfony/mynamespace'));
    $this->assertEqual(null, $ph->get('myfoo'));

    // test multi-delemsional get
    $ph = new sfParameterHolder();
    $ph->add(array('foo' => array(
      'bar' => array(
        'baz' => 'foo bar',
      ),
      'bars' => array('foo', 'bar'),
    )));
    $this->assertEqual('foo bar', $ph->get('foo[bar][baz]'));
    $this->assertEqual('bar', $ph->get('foo[bars][1]'));
    $this->assertEqual(null, $ph->get('foo[bars][2]'));
    $this->assertEqual(array('foo', 'bar'), $ph->get('foo[bars][]'));
    $this->assertEqual($ph->get('foo[bars][]'), $ph->get('foo[bars]')); // these should be equal
  }

  public function test_getNames()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('yourfoo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $this->assertEqual(array('foo', 'yourfoo'), $ph->getNames());
    $this->assertEqual(array('myfoo'), $ph->getNames('symfony/mynamespace'));
  }

  public function test_getNamespaces()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('yourfoo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $this->assertEqual(array($ph->getDefaultNamespace(), 'symfony/mynamespace'), $ph->getNamespaces());
  }

  public function test_getAll()
  {
    $parameters = array('foo' => 'bar', 'myfoo' => 'bar');

    $ph = new sfParameterHolder();
    $ph->add($parameters);
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $this->assertEqual($parameters, $ph->getAll());
  }

  public function test_has()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $this->assertTrue($ph->has('foo'));
    $this->assertFalse($ph->has('bar'));
    $this->assertFalse($ph->has('myfoo'));
    $this->assertTrue($ph->has('myfoo', 'symfony/mynamespace'));

    // test multi-delemsional has
    $ph = new sfParameterHolder();
    $ph->add(array('foo' => array(
      'bar' => array(
        'baz' => 'foo bar',
      ),
      'bars' => array('foo', 'bar'),
    )));
    $this->assertEqual(true, $ph->has('foo[bar][baz]'));
    $this->assertEqual(true, $ph->get('foo[bars][1]'));
    $this->assertEqual(false, $ph->get('foo[bars][2]'));
    $this->assertEqual(true, $ph->has('foo[bars][]'));
    $this->assertEqual($ph->get('foo[bars][]'), $ph->has('foo[bars]')); // these should be equal
  }

  public function test_hasNamespace()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $this->assertTrue($ph->hasNamespace($ph->getDefaultNamespace()));
    $this->assertTrue($ph->hasNamespace('symfony/mynamespace'));
  }

  public function test_remove()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('myfoo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $ph->remove('foo');
    $this->assertFalse($ph->has('foo'));

    $ph->remove('myfoo');
    $this->assertFalse($ph->has('myfoo'));
    $this->assertTrue($ph->has('myfoo', 'symfony/mynamespace'));

    $ph->remove('myfoo', 'symfony/mynamespace');
    $this->assertFalse($ph->has('myfoo', 'symfony/mynamespace'));

    $this->assertEqual(null, $ph->getAll());
  }

  public function test_removeNamespace()
  {
    $ph = new sfParameterHolder();
    $ph->set('foo', 'bar');
    $ph->set('myfoo', 'bar');
    $ph->set('myfoo', 'bar', 'symfony/mynamespace');

    $ph->removeNamespace($ph->getDefaultNamespace());
    $this->assertFalse($ph->has('foo'));
    $this->assertFalse($ph->has('myfoo'));
    $this->assertTrue($ph->has('myfoo', 'symfony/mynamespace'));

    $ph->removeNamespace('symfony/mynamespace');
    $this->assertFalse($ph->has('myfoo', 'symfony/mynamespace'));

    $this->assertEqual(null, $ph->getAll());
  }

  public function test_set()
  {
    $foo = 'bar';

    $ph = new sfParameterHolder();
    $ph->set('foo', $foo);
    $this->assertEqual($foo, $ph->get('foo'));

    $foo = 'foo';
    $this->assertEqual('bar', $ph->get('foo'));

    $ph->set('myfoo', 'bar', 'symfony/mynamespace');
    $this->assertEqual('bar', $ph->get('myfoo', null, 'symfony/mynamespace'));
  }

  public function test_setByRef()
  {
    $foo = 'bar';

    $ph = new sfParameterHolder();
    $ph->setByRef('foo', $foo);
    $this->assertEqual($foo, $ph->get('foo'));

    $foo = 'foo';
    $this->assertEqual($foo, $ph->get('foo'));

    $myfoo = 'bar';
    $ph->setByRef('myfoo', $myfoo, 'symfony/mynamespace');
    $this->assertEqual($myfoo, $ph->get('myfoo', null, 'symfony/mynamespace'));
  }

  public function test_add()
  {
    $foo = 'bar';
    $parameters = array('foo' => $foo, 'bar' => 'bar');
    $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

    $ph = new sfParameterHolder();
    $ph->add($parameters);
    $ph->add($myparameters, 'symfony/mynamespace');

    $this->assertEqual($parameters, $ph->getAll());
    $this->assertEqual($myparameters, $ph->getAll('symfony/mynamespace'));

    $foo = 'mybar';
    $this->assertEqual($parameters, $ph->getAll());
  }

  public function test_addByRef()
  {
    $foo = 'bar';
    $parameters = array('foo' => &$foo, 'bar' => 'bar');
    $myparameters = array('myfoo' => 'bar', 'mybar' => 'bar');

    $ph = new sfParameterHolder();
    $ph->addByRef($parameters);
    $ph->addByRef($myparameters, 'symfony/mynamespace');

    $this->assertEqual($parameters, $ph->getAll());
    $this->assertEqual($myparameters, $ph->getAll('symfony/mynamespace'));

    $foo = 'mybar';
    $this->assertEqual($parameters, $ph->getAll());
  }
}
