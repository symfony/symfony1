<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

// exceptions
$b->
  get('/exception/noException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'noException')->
  responseContains('foo')->

  get('/exception/throwsException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception')->
  throwsException('Exception', 'Exception message')->
  throwsException('Exception', '/message/')->
  throwsException(null, '!/sfException/')->

  get('/exception/throwsSfException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsSfException')->
  throwsException('sfException')->
  throwsException('sfException', 'sfException message')
;
