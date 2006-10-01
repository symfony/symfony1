<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../bootstrap.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

class myRequest extends sfRequest
{
  function shutdown() {}
}

class fakeRequest
{
}

$t = new lime_test(53, new lime_output_color());

$context = new sfContext();
sfRouting::getInstance()->clearRoutes();

// ::newInstance()
$t->diag('::newInstance()');
$t->isa_ok(sfRequest::newInstance('myRequest'), 'myRequest', '::newInstance() takes a request class as its first parameter');
$t->isa_ok(sfRequest::newInstance('myRequest'), 'myRequest', '::newInstance() returns an instance of myRequest');

try
{
  sfRequest::newInstance('fakeRequest');
  $t->fail('::newInstance() throws a sfFactoryException if the class does not extends sfRequest');
}
catch (sfFactoryException $e)
{
  $t->pass('::newInstance() throws a sfFactoryException if the class does not extends sfRequest');
}

// ->initialize()
$t->diag('->initialize()');
$request = sfRequest::newInstance('myRequest');
$t->is($request->getContext(), null, '->initialize() takes a sfContext object as its first argument');
$request->initialize($context, array('foo' => 'bar'));
$t->is($request->getParameter('foo'), 'bar', '->initialize() takes an array of parameters as its second argument');

// ->getContext()
$t->diag('->getContext()');
$request->initialize($context);
$t->is($request->getContext(), $context, '->getContext() returns the current context');

// ->getMethod() ->setMethod()
$t->diag('->getMethod() ->setMethod()');
$request->setMethod(sfRequest::GET);
$t->is($request->getMethod(), sfRequest::GET, '->getMethod() returns the current request method');

try
{
  $request->setMethod('foo');
  $t->fail('->setMethod() throws a sfException if the method is not valid');
}
catch (sfException $e)
{
  $t->pass('->setMethod() throws a sfException if the method is not valid');
}

// ->extractParameters()
$t->diag('->extractParameters()');
$request->initialize($context, array('foo' => 'foo', 'bar' => 'bar'));
$t->is($request->extractParameters(array()), array(), '->extractParameters() returns parameters');
$t->is($request->extractParameters(array('foo')), array('foo' => 'foo'), '->extractParameters() returns parameters for keys in its first parameter');
$t->is($request->extractParameters(array('bar')), array('bar' => 'bar'), '->extractParameters() returns parameters for keys in its first parameter');

$request = sfRequest::newInstance('myRequest');
$request->initialize($context);

// ->setError() ->hasError() ->hasErrors() ->getError() ->removeError() ->getErrorNames
$t->diag('->setError() ->hasError() ->hasErrors() ->getError() ->removeError() ->getErrorNames');

// single error
$key = "test";
$value = "error";

$request->setError($key, $value, '->setError() add an error message for the given parameter');
$t->is($request->hasError($key), true, '->hasError() returns true if an error exists for the given parameter');
$t->is($request->hasErrors(), true, '->hasErrors() returns true if there are some errors');
$t->is($request->getError($key), $value, '->getError() returns the error text for the given parameter');
$t->is($request->removeError($key), $value, '->removeError() removes the error for the given parameter');
$t->is($request->hasError($key), false, '->hasError() returns false if no error exists for the given parameter');
$t->is($request->hasErrors(), false, '->hasErrors() returns false if there is no error');

// multiple errors
$key1 = "test1";
$value_key1_1 = "error1_1";
$value_key1_2 = "error1_2";
$key2 = "test 2";
$value_key2_1 = "error2_1";
$array_errors = array($key1 => $value_key1_2, $key2 => $value_key2_1);
$error_names = array($key1, $key2);

$request->setError($key1, $value_key1_1, '->setError() add an error message for the given parameter');
$request->setErrors($array_errors, '->setErrors() add an array of error messages');
$t->is($request->hasError($key1), true, '->hasError() returns true if an error exists for the given parameter');
$t->is($request->hasErrors(), true, '->hasErrors() returns true if there are some errors');
$t->is($request->getErrorNames(), $error_names, '->getErrorName() returns an array of error names');
$t->is($request->getErrors(), $array_errors, '->hasErrors() returns true if there are some errors');
$t->is($request->getError($key1), $value_key1_2, '->getError() returns the error text for the given parameter');
$t->is($request->removeError($key1), $value_key1_2, '->removeError() removes the error for the given parameter');
$t->is($request->hasErrors(), true, '->hasErrors() returns true if there are some errors');
$t->is($request->removeError($key2), $value_key2_1, '->removeError() removes the error for the given parameter');
$t->is($request->hasErrors(), false, '->hasErrors() returns false if there is no error');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'parameter');

// attribute holder proxy
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'attribute');

// mixins
require_once($_test_dir.'/unit/sfMixerTest.class.php');
$mixert = new sfMixerTest($t);
$mixert->launchTests($request, 'sfRequest');
