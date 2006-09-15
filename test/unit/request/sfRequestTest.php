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

$t = new lime_test(42, new lime_output_color());

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
$t->diag('->getMethod() ->setMethod()');
$request->setMethod(sfRequest::GET);
$t->is($request->getMethod(), sfRequest::GET, '->getMethod() returns the current request method');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'parameter');

// attribute holder proxy
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'attribute');
