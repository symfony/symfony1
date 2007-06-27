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

$t = new lime_test(12, new lime_output_color());

sfConfig::set('sf_default_module', 'main');
sfConfig::set('sf_default_action', 'index');

$context = sfContext::getInstance(array('routing' => 'sfNoRouting', 'request' => 'sfWebRequest'));
$request = $context->request;
$routing = $context->routing;

// ->getCurrentInternalUri()
$t->diag('->getCurrentInternalUri()');

$_GET = array();
$request->initialize($context);
$t->is($routing->getCurrentInternalUri(), 'main/index', '->getCurrentInternalUri() returns the current internal URI');

$_GET = array('foo' => 'bar');
$request->initialize($context);
$t->is($routing->getCurrentInternalUri(), 'main/index?foo=bar', '->getCurrentInternalUri() returns the current internal URI');

$_GET = array('module' => 'foo', 'action' => 'bar');
$request->initialize($context);
$t->is($routing->getCurrentInternalUri(), 'foo/bar', '->getCurrentInternalUri() returns the current internal URI');

$_GET = array('module' => 'foo', 'action' => 'bar', 'foo' => 'bar');
$request->initialize($context);
$t->is($routing->getCurrentInternalUri(), 'foo/bar?foo=bar', '->getCurrentInternalUri() returns the current internal URI');

// ->parse()
$t->diag('parse');
$t->is($routing->parse(''), array('module' => 'main', 'action' => 'index'), '->parse() parses a URL');
$t->is($routing->parse('?foo=bar'), array('foo' => 'bar', 'module' => 'main', 'action' => 'index'), '->parse() parses a URL');
$t->is($routing->parse('?module=foo&action=bar'), array('module' => 'foo', 'action' => 'bar'), '->parse() parses a URL');
$t->is($routing->parse('?module=foo&action=bar&foo=bar'), array('foo' => 'bar', 'module' => 'foo', 'action' => 'bar'), '->parse() parses a URL');

// ->generate()
$t->diag('->generate()');
$t->is($routing->generate(null, array()), '/', '->generate() generates a URL from an array of parameters');
$t->is($routing->generate(null, array('foo' => 'bar')), '/?foo=bar', '->generate() generates a URL from an array of parameters');
$t->is($routing->generate(null, array('module' => 'foo', 'action' => 'bar')), '/?module=foo&action=bar', '->generate() generates a URL from an array of parameters');
$t->is($routing->generate(null, array('module' => 'foo', 'action' => 'bar', 'foo' => 'bar')), '/?module=foo&action=bar&foo=bar', '->generate() generates a URL from an array of parameters');
