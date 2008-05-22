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

  get('/exception/throwsException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception', '/Exception message/')->

  get('/exception/throwsException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception', '/message/')->

  get('/exception/throwsException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException(null, '!/sfException/')->

  get('/exception/throwsSfException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsSfException')->
  throwsException('sfException')->

  get('/exception/throwsSfException')->
  isStatusCode(200)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsSfException')->
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

// check persistence of root directory
$rootDir = $b->getContext()->getConfiguration()->getRootDir();
$b->getContext()->getConfiguration()->setRootDir('/');
try
{
  $b->get('/browser');
  $b->test()->fail('root directory persists across multiple calls');
}
catch (Exception $e)
{
  $b->test()->pass('root directory persists across multiple calls');
}
$b->getContext()->getConfiguration()->setRootDir($rootDir);

// check persistence of event listeners
$b->getContext()->getEventDispatcher()->connect('my_event', 'a_fake_callable');
$b->get('/browser');
$b->test()->ok($b->getContext()->getEventDispatcher()->hasListeners('my_event'), 'event listeners persist across multiple requests');

// check consistency of number of listeners
$nb = count($b->getContext()->getEventDispatcher()->getListeners('application.throw_exception'));
$b->get('/browser');
$b->test()->is(count($b->getContext()->getEventDispatcher()->getListeners('application.throw_exception')), $nb, 'event listeners are not duplicated');
