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
require_once($_test_dir.'/../lib/view/sfView.class.php');

$t = new lime_test(13, new lime_output_color());

class myView extends sfView
{
  function execute () {}
  function configure () {}
  function getEngine () {}
  function render ($templateVars = null) {}
}

$context = new sfContext();
$view = new myView();
$view->initialize($context, '', '', '');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($view, 'parameter');
