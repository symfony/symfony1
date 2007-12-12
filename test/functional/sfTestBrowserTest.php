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
  throwsException('Exception', '/Exception message/')->
  throwsException('Exception', '/message/')->
  throwsException(null, '!/sfException/')->

  get('/exception/throwsSfException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsSfException')->
  throwsException('sfException')->
  throwsException('sfException', 'sfException message')
;

$b->
  get('/browser')->
  responseContains('html')->
  checkResponseElement('h1', 'html')->

  get('/browser/text')->
  responseContains('text')
;

try
{
  $b->checkResponseElement('h1', 'text');
  $b->test()->fail('The DOM is not accessible if the response content type is not HTML');
}
catch (sfException $e)
{
  $b->test()->pass('The DOM is not accessible if the response content type is not HTML');
}

// check response headers
$b->
  get('/browser/responseHeader')->
  isStatusCode()->
  isResponseHeader('content-type', 'text/plain; charset=utf-8')->
  isResponseHeader('foo', 'bar')->
  isResponseHeader('foo', 'foobar')
;
