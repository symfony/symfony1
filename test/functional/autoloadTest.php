<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
$ret = include(dirname(__FILE__).'/../bootstrap/functional.php');
if (!$ret)
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

$b->
  get('/autoload/myAutoload')->
  isStatusCode(200)->
  isRequestParameter('module', 'autoload')->
  isRequestParameter('action', 'myAutoload')->
  checkResponseElement('body div', 'foo')
;
