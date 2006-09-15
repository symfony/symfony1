<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please component the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/request/sfRequest.class.php');
require_once($_test_dir.'/../lib/request/sfWebRequest.class.php');
require_once($_test_dir.'/../lib/response/sfResponse.class.php');
require_once($_test_dir.'/../lib/response/sfWebResponse.class.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/util/sfParameterHolder.class.php');
require_once($_test_dir.'/../lib/action/sfComponent.class.php');

$t = new lime_test(1, new lime_output_color());

class myComponent extends sfComponent
{
  function execute () {}
}

$context = new sfContext();
$component = new myComponent();
$component->initialize($context);

// mixins
require_once($_test_dir.'/unit/sfMixerTest.class.php');
$mixert = new sfMixerTest($t);
$mixert->launchTests($component, 'sfComponent');
