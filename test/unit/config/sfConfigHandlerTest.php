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

$t = new lime_test(3, new lime_output_color());

class myConfigHandler extends sfConfigHandler
{
  public function execute($configFiles) {}

  public static function replaceConstantsCallback(&$value)
  {
    return parent::replaceConstantsCallback($value);
  }
}

$context = new sfContext();
$config = new myConfigHandler();
$config->initialize($context);

// ::replaceConstantsCallback()
$t->diag('::replaceConstantsCallback()');

sfConfig::set('foo', 'bar');
$value = 'my value with a %foo% constant';
myConfigHandler::replaceConstantsCallback($value);
$t->is($value, 'my value with a bar constant', '::replaceConstantsCallback() replaces constants enclosed in %');

$value = '%Y/%m/%d %H:%M';
myConfigHandler::replaceConstantsCallback($value);
$t->is($value, '%Y/%m/%d %H:%M', '::replaceConstantsCallback() does not replace unknown constants');

// ->getParameterHolder()
$t->diag('->getParameterHolder()');
$t->isa_ok($config->getParameterHolder(), 'sfParameterHolder', "->getParameterHolder() returns a parameter holder instance");
