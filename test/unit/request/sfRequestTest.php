<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/util/sfParameterHolder.class.php');
require_once($_test_dir.'/../lib/request/sfRequest.class.php');
require_once($_test_dir.'/../lib/request/sfWebRequest.class.php');
require_once($_test_dir.'/../lib/controller/sfRouting.class.php');

$t = new lime_test(30, new lime_output_color());

sfRouting::getInstance()->clearRoutes();

// can't initialize directly the sfRequest class (abstract)
// using sfWebRequest class to test sfRequest

sfConfig::set('sf_path_info_array', 'SERVER');
sfConfig::set('sf_path_info_key', true);
sfConfig::set('sf_logging_active', false);
sfConfig::set('sf_i18n', 0);
//$this->populateVariables('/', true);

$context = new sfContext();
$request = sfRequest::newInstance('sfWebRequest');
$request->initialize($context);

// single error
$key = "test";
$value = "error";

$request->setError($key, $value);
$t->is($request->hasError($key), true);
$t->is($request->hasErrors(), true);
$t->is($request->getError($key), $value);
$t->is($request->removeError($key), $value);
$t->is($request->hasError($key), false);
$t->is($request->hasErrors(), false);

// multiple errors
$key1 = "test1";
$value_key1_1 = "error1_1";
$value_key1_2 = "error1_2";
$key2 = "test 2";
$value_key2_1 = "error2_1";
$array_errors = array($key1 => $value_key1_2, $key2 => $value_key2_1);
$error_names = array($key1, $key2);

$request->setError($key1, $value_key1_1);
$request->setErrors($array_errors);
$t->is($request->hasError($key1), true);
$t->is($request->hasErrors(), true);
$t->is($request->getErrorNames(), $error_names);
$t->is($request->getErrors(), $array_errors);
$t->is($request->getError($key1), $value_key1_2);
$t->is($request->removeError($key1), $value_key1_2);
$t->is($request->hasErrors(), true);
$t->is($request->removeError($key2), $value_key2_1);
$t->is($request->hasErrors(), false);

// ->getMethod() ->setMethod()
$request->setMethod(sfRequest::GET);
$t->is($request->getMethod(), sfRequest::GET, '->getMethod() returns the current request method');

// parameter holder
$name1 = 'test_name1';
$value1 = 'test_value1';
$name2 = 'test_name2';
$value2 = 'test_value2';
$ns = 'test_ns';
$t->is($request->hasParameter($name1), false);
$t->is($request->getParameter($name1, $value1), $value1);
$request->setParameter($name1, $value1);
$t->is($request->hasParameter($name1), true);
$t->is($request->getParameter($name1), $value1);
$request->setParameter($name2, $value2, $ns);
$t->is($request->hasParameter($name2), false);
$t->is($request->hasParameter($name2, $ns), true);
$t->is($request->getParameter($name2, '', $ns), $value2);

// attribute holder
$name1 = 'test_name1';
$value1 = 'test_value1';
$name2 = 'test_name2';
$value2 = 'test_value2';
$ns = 'test_ns';
$t->is($request->hasAttribute($name1), false);
$t->is($request->getAttribute($name1, $value1), $value1);
$request->setAttribute($name1, $value1);
$t->is($request->hasAttribute($name1), true);
$t->is($request->getAttribute($name1), $value1);
$request->setAttribute($name2, $value2, $ns);
$t->is($request->hasAttribute($name2), false);
$t->is($request->hasAttribute($name2, $ns), true);
$t->is($request->getAttribute($name2, '', $ns), $value2);
