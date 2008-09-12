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
require_once(dirname(__FILE__).'/../../../lib/helper/PartialHelper.php');

// Fixme: make this test more beautiful and extend it

$t = new lime_test(1, new lime_output_color());

class MyTestPartialView extends sfPartialView
{

  public function render()
  {
    //used to check if this class was used
    return 'MyPartialView';
  }
  public function initialize($context, $moduleName, $actionName, $viewName)
  {
    //basic dummy so far
  }
  public function setPartialVars(array $partialVars)
  {
    //basic dummy so far
  }

}

$t->diag('->get_partial()');
sfConfig::set('mod_MODULE_partial_view_class', 'MyTest');
$t->is(get_partial('MODULE/dummy'), 'MyPartialView', 'get_partial() uses the class specified in partial_view_class for the given module');

