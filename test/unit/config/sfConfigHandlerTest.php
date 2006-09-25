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
require_once($_test_dir.'/unit/bootstrap.php');

$t = new lime_test(6, new lime_output_color());

class myConfigHandler extends sfConfigHandler
{
  public function execute($configFiles) {}
}

$context = new sfContext();
$config = new myConfigHandler();
$config->initialize($context);

// ->initialize()
$t->diag('->initialize()');
$config->initialize(array('foo' => 'bar'));
$t->is($config->getParameterHolder()->get('foo'), 'bar', '->initialize() takes an array of parameters as its first argument');

// ::replaceConstants()
$t->diag('::replaceConstants()');
sfConfig::set('foo', 'bar');
$t->is(sfConfigHandler::replaceConstants('my value with a %foo% constant'), 'my value with a bar constant', '::replaceConstants() replaces constants enclosed in %');

$t->is(sfConfigHandler::replaceConstants('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstants() does not replace unknown constants');

sfConfig::set('foo', 'bar');
$value = array(
  'foo' => 'my value with a %foo% constant',
  'bar' => array(
    'foo' => 'my value with a %foo% constant',
  ),
);
$value = sfConfigHandler::replaceConstants($value);
$t->is($value['foo'], 'my value with a bar constant', '::replaceConstants() replaces constants in arrays recursively');
$t->is($value['bar']['foo'], 'my value with a bar constant', '::replaceConstants() replaces constants in arrays recursively');

// ->getParameterHolder()
$t->diag('->getParameterHolder()');
$t->isa_ok($config->getParameterHolder(), 'sfParameterHolder', "->getParameterHolder() returns a parameter holder instance");
