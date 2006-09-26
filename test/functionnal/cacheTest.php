<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'cache';

include(dirname(__FILE__).'/bootstrap.php');

class myTestBrowser extends sfTestBrowser
{
}

$b = new myTestBrowser();
$b->initialize();

// default page is in cache (without layout)
$b->
  get('/')->
  isStatusCode(200)->
  isRequestParameter('module', 'default')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/congratulations/i')->
  isCached(true)
;

$b->
  get('/nocache')->
  isStatusCode(200)->
  isRequestParameter('module', 'nocache')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/nocache/i')->
  isCached(false)
;

$b->
  get('/cache/page')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'page')->
  checkResponseElement('body', '/page in cache/')->
  isCached(true, true)
;

$b->
  get('/cache/forward')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'forward')->
  checkResponseElement('body', '/page in cache/')->
  isCached(true)
;

$b->
  get('/cache/multi')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'multi')->
  checkResponseElement('div#cacheablePartial', '/IN CACHEABLE PARTIAL/')->
  checkResponseElement('div#cacheablePartialbar', '/IN CACHEABLE PARTIALbar/')->
  checkResponseElement('div#partial', '/IN PARTIAL/')->
  checkResponseElement('div#cacheableComponent', '/IN CACHEABLE COMPONENT/')->
  checkResponseElement('div#cacheableComponentbar', '/IN CACHEABLE COMPONENTbar/')->
  checkResponseElement('div#component', '/IN COMPONENT/')->
  isCached(false)->
  isUriCached('cache/_cacheablePartial?key='.md5(serialize(array())), true)->
  isUriCached('cache/_cacheablePartial?key='.md5(serialize(array('foo' => 'bar'))), true)->
  isUriCached('cache/_cacheablePartial?key='.md5(serialize(array('foo' => 'foo'))), false)->
  isUriCached('cache/_partial?key='.md5(serialize(array())), false)->
  isUriCached('cache/_cacheableComponent?key='.md5(serialize(array())), true)->
  isUriCached('cache/_cacheableComponent?key='.md5(serialize(array('foo' => 'bar'))), true)->
  isUriCached('cache/_cacheableComponent?key='.md5(serialize(array('foo' => 'foo'))), false)->
  isUriCached('cache/_component?key='.md5(serialize(array())), false)
;

$b->
  get('/cache/multi/bar/foo')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'multi')->
  checkResponseElement('div#cacheableComponentfoo', '/IN CACHEABLE COMPONENTfoo/')->
  checkResponseElement('div#cacheableComponentbarfoo', '/IN CACHEABLE COMPONENTbarfoo/')
;
