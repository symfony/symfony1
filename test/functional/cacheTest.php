<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'cache';
$ret = include(dirname(__FILE__).'/../bootstrap/functional.php');
if (!$ret)
{
  return;
}

class myTestBrowser extends sfTestBrowser
{
  function getMultiAction($parameter = null)
  {
    return $this->
      get('/cache/multi'.(null !== $parameter ? '/param/'.$parameter : ''))->
      isStatusCode(200)->
      isRequestParameter('module', 'cache')->
      isRequestParameter('action', 'multi')->
      isCached(false)->

      // partials
      checkResponseElement('#partial .partial')->

      // contextual partials
      checkResponseElement('#contextualPartial .contextualPartial')->
      checkResponseElement('#contextualCacheablePartial .contextualCacheablePartial__'.$parameter)->
      checkResponseElement('#contextualCacheablePartialVarParam .contextualCacheablePartial_varParam_'.$parameter)->

      // components
      checkResponseElement('#component .component__componentParam_'.$parameter)->
      checkResponseElement('#componentVarParam .component_varParam_componentParam_'.$parameter)->

      // contextual components
      checkResponseElement('#contextualComponent .contextualComponent__componentParam_'.$parameter)->
      checkResponseElement('#contextualComponentVarParam .contextualComponent_varParam_componentParam_'.$parameter)->
      checkResponseElement('#contextualCacheableComponent .contextualCacheableComponent__componentParam_'.$parameter)->
      checkResponseElement('#contextualCacheableComponentVarParam .contextualCacheableComponent_varParam_componentParam_'.$parameter)->

      // partial cache
      isUriCached('@sf_cache_partial?module=cache&action=_partial&sf_cache_key='.md5(serialize(array())), false)->
      isUriCached('@sf_cache_partial?module=cache&action=_partial&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), false)->

      isUriCached('@sf_cache_partial?module=cache&action=_cacheablePartial&sf_cache_key='.md5(serialize(array())), true)->
      isUriCached('@sf_cache_partial?module=cache&action=_cacheablePartial&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), true)->

      isUriCached('@sf_cache_partial?module=cache&action=_cacheablePartial&sf_cache_key='.md5(serialize(array('varParam' => 'another'))), false)->

      // contextual partial cache
      isUriCached('@sf_cache_partial?module=cache&action=_contextualPartial&sf_cache_key='.md5(serialize(array())), false)->
      isUriCached('@sf_cache_partial?module=cache&action=_contextualPartial&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), false)->

      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheablePartial&sf_cache_key='.md5(serialize(array())), true)->
      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheablePartial&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), true)->

      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheablePartial&sf_cache_key='.md5(serialize(array('varParam' => 'another'))), false)->

      // component cache
      isUriCached('@sf_cache_partial?module=cache&action=_component&sf_cache_key='.md5(serialize(array())), false)->
      isUriCached('@sf_cache_partial?module=cache&action=_component&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), false)->

      isUriCached('@sf_cache_partial?module=cache&action=_cacheableComponent&sf_cache_key='.md5(serialize(array())), true)->
      isUriCached('@sf_cache_partial?module=cache&action=_cacheableComponent&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), true)->

      isUriCached('@sf_cache_partial?module=cache&action=_cacheableComponent&sf_cache_key='.md5(serialize(array('varParam' => 'another'))), false)->

      // contextual component cache
      isUriCached('@sf_cache_partial?module=cache&action=_contextualComponent&sf_cache_key='.md5(serialize(array())), false)->
      isUriCached('@sf_cache_partial?module=cache&action=_contextualComponent&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), false)->

      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key='.md5(serialize(array())), true)->
      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key='.md5(serialize(array('varParam' => 'varParam'))), true)->

      isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key='.md5(serialize(array('varParam' => 'another'))), false)
    ;
  }
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

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

$b->
  getMultiAction()->

  getMultiAction('requestParam')->

  // component already in cache and not contextual, so request parameter is not there
  checkResponseElement('#cacheableComponent .cacheableComponent__componentParam_')->
  checkResponseElement('#cacheableComponentVarParam .cacheableComponent_varParam_componentParam_')->
  checkResponseElement('#cacheablePartial .cacheablePartial__')->
  checkResponseElement('#cacheablePartialVarParam .cacheablePartial_varParam_')
;

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

$b->
  getMultiAction('requestParam')->

  checkResponseElement('#cacheableComponent .cacheableComponent__componentParam_requestParam')->
  checkResponseElement('#cacheableComponentVarParam .cacheableComponent_varParam_componentParam_requestParam')->
  checkResponseElement('#cacheablePartial .cacheablePartial__requestParam')->
  checkResponseElement('#cacheablePartialVarParam .cacheablePartial_varParam_requestParam')->

  getMultiAction()->

  checkResponseElement('#cacheableComponent .cacheableComponent__componentParam_requestParam')->
  checkResponseElement('#cacheableComponentVarParam .cacheableComponent_varParam_componentParam_requestParam')->
  checkResponseElement('#cacheablePartial .cacheablePartial__requestParam')->
  checkResponseElement('#cacheablePartialVarParam .cacheablePartial_varParam_requestParam')->

  getMultiAction('anotherRequestParam')->

  checkResponseElement('#cacheableComponent .cacheableComponent__componentParam_requestParam')->
  checkResponseElement('#cacheableComponentVarParam .cacheableComponent_varParam_componentParam_requestParam')->
  checkResponseElement('#cacheablePartial .cacheablePartial__requestParam')->
  checkResponseElement('#cacheablePartialVarParam .cacheablePartial_varParam_requestParam')
;

// check contextual cache with another action
$b->
  get('/cache/multiBis')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'multiBis')->
  isCached(false)->

  // partials
  checkResponseElement('#cacheablePartial .cacheablePartial__requestParam')->

  // contextual partials
  checkResponseElement('#contextualCacheablePartial .contextualCacheablePartial__')->

  // components
  checkResponseElement('#cacheableComponent .cacheableComponent__componentParam_requestParam')->

  // contextual components
  checkResponseElement('#contextualCacheableComponent .contextualCacheableComponent__componentParam_')->

  // partial cache
  isUriCached('@sf_cache_partial?module=cache&action=_cacheablePartial&sf_cache_key='.md5(serialize(array())), true)->

  // contextual partial cache
  isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key='.md5(serialize(array())), true)->

  // component cache
  isUriCached('@sf_cache_partial?module=cache&action=_cacheableComponent&sf_cache_key='.md5(serialize(array())), true)->

  // contextual component cache
  isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key='.md5(serialize(array())), true)
;

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

// check user supplied cache key for partials and components
$b->
  get('/cache/specificCacheKey')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'specificCacheKey')->
  isCached(false)->

  // partial cache
  isUriCached('@sf_cache_partial?module=cache&action=_cacheablePartial&sf_cache_key=cacheablePartial', true)->

  // contextual partial cache
  isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key=contextualCacheableComponent', true)->

  // component cache
  isUriCached('@sf_cache_partial?module=cache&action=_cacheableComponent&sf_cache_key=cacheableComponent', true)->

  // contextual component cache
  isUriCached('@sf_cache_partial?module=cache&action=_contextualCacheableComponent&sf_cache_key=contextualCacheableComponent', true)
;

// check cache content for actions

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

$b->
  get('/cache/action')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'action')->
  isCached(true)
;

$b->test()->is(sfConfig::get('ACTION_EXECUTED', false), true, 'action is executed when not in cache');
sfConfig::set('ACTION_EXECUTED', false);

$response = $b->getResponse();
$content1 = $response->getContent();
$contentType1 = $response->getContentType();
$headers1 = $response->getHttpHeaders();

$b->
  get('/cache/action')->
  isStatusCode(200)->
  isRequestParameter('module', 'cache')->
  isRequestParameter('action', 'action')->
  isCached(true)
;

$b->test()->is(sfConfig::get('ACTION_EXECUTED', false), false, 'action is not executed when in cache');

$response = $b->getResponse();
$content2 = $response->getContent();
$contentType2 = $response->getContentType();
$headers2 = $response->getHttpHeaders();

$b->test()->is($content1, $content2, 'response content is the same');
$b->test()->is($contentType1, $contentType2, 'response content type is the same');
$b->test()->is($headers1, $headers2, 'response http headers are the same');
