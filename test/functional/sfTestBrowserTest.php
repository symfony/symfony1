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
  isStatusCode(500)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception')->

  get('/exception/throwsException')->
  isStatusCode(500)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception', '/Exception message/')->

  get('/exception/throwsException')->
  isStatusCode(500)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException('Exception', '/message/')->

  get('/exception/throwsException')->
  isStatusCode(500)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsException')->
  throwsException(null, '!/sfException/')->

  get('/exception/throwsSfException')->
  isStatusCode(500)->
  isRequestParameter('module', 'exception')->
  isRequestParameter('action', 'throwsSfException')->
  throwsException('sfException')->

  get('/exception/throwsSfException')->
  isStatusCode(500)->
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
catch (LogicException $e)
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

// cookies
$b->
  setCookie('foo', 'bar')->
  setCookie('bar', 'foo')->
  setCookie('foofoo', 'foo', time() - 10)->

  get('/cookie')->
  hasCookie('foofoo', false)->
  hasCookie('foo')->
  isCookie('foo', 'bar')->
  isCookie('foo', '/a/')->
  isCookie('foo', '!/z/')->
  checkResponseElement('p', 'bar.foo-')->
  get('/cookie')->
  hasCookie('foo')->
  isCookie('foo', 'bar')->
  isCookie('foo', '/a/')->
  isCookie('foo', '!/z/')->
  checkResponseElement('p', 'bar.foo-')->
  removeCookie('foo')->
  get('/cookie')->
  hasCookie('foo', false)->
  hasCookie('bar')->
  checkResponseElement('p', '.foo-')->
  clearCookies()->
  get('/cookie')->
  hasCookie('foo', false)->
  hasCookie('bar', false)->
  checkResponseElement('p', '.-')
;

$b->
  setCookie('foo', 'bar')->
  setCookie('bar', 'foo')->

  get('/cookie/setCookie')->

  get('/cookie')->
  hasCookie('foo')->
  isCookie('foo', 'bar')->
  isCookie('foo', '/a/')->
  isCookie('foo', '!/z/')->
  checkResponseElement('p', 'bar.foo-barfoo')->
  get('/cookie')->
  hasCookie('foo')->
  isCookie('foo', 'bar')->
  isCookie('foo', '/a/')->
  isCookie('foo', '!/z/')->
  checkResponseElement('p', 'bar.foo-barfoo')->
  removeCookie('foo')->
  get('/cookie')->
  hasCookie('foo', false)->
  hasCookie('bar')->
  checkResponseElement('p', '.foo-barfoo')->

  get('/cookie/removeCookie')->

  get('/cookie')->
  hasCookie('foo', false)->
  hasCookie('bar')->
  checkResponseElement('p', '.foo-')->

  get('/cookie/setCookie')->

  clearCookies()->
  get('/cookie')->
  hasCookie('foo', false)->
  hasCookie('bar', false)->
  checkResponseElement('p', '.-')
;

$b->
  get('/browser')->
  isRequestMethod('get')->
  post('/browser')->
  isRequestMethod('post')->
  call('/browser', 'put')->
  isRequestMethod('put');
