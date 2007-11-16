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

// default main page
$b->
  get('/')->
  isStatusCode(200)->
  isRequestParameter('module', 'default')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/congratulations/i')->
  checkResponseElement('link[href="/sf/sf_default/css/screen.css"]')
;

// default 404
$b->
  get('/nonexistant')->
  isStatusCode(404)->
  isForwardedTo('default', 'error404')->
  checkResponseElement('body', '!/congratulations/i')->
  checkResponseElement('link[href="/sf/sf_default/css/screen.css"]')
;

// 404 with ETag enabled must returns 404, not 304
sfConfig::set('sf_cache', true);
sfConfig::set('sf_etag', true);
$b->
  get('/notfound')->
  isStatusCode(404)->
  isRequestParameter('module', 'notfound')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/404/')->

  get('/notfound')->
  isStatusCode(404)->
  isRequestParameter('module', 'notfound')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/404/')
;
sfConfig::set('sf_cache', false);
sfConfig::set('sf_etag', false);

// unexistant action
$b->
  get('/default/nonexistantaction')->
  isStatusCode(404)->
  isForwardedTo('default', 'error404')->
  checkResponseElement('link[href="/sf/sf_default/css/screen.css"]')
;

// module.yml: enabled
$b->
  get('/configModuleDisabled')->
  isStatusCode(200)->
  isForwardedTo('default', 'disabled')->
  checkResponseElement('body', '/module is unavailable/i')->
  checkResponseElement('body', '!/congratulations/i')->
  checkResponseElement('link[href="/sf/sf_default/css/screen.css"]')
;

// view.yml: has_layout
$b->
  get('/configViewHasLayout/withoutLayout')->
  isStatusCode(200)->
  checkResponseElement('body', '/no layout/i')->
  checkResponseElement('head title', false)
;

// security.yml: is_secure
$b->
  get('/configSecurityIsSecure')->
  isStatusCode(200)->
  isForwardedTo('default', 'login')->
  checkResponseElement('body', '/Login Required/i')->
  // check that there is no double output caused by the forwarding in a filter
  checkResponseElement('body', 1)->
  checkResponseElement('link[href="/sf/sf_default/css/screen.css"]')
;

// security.yml: case sensitivity
$b->
  get('/configSecurityIsSecureAction/index')->
  isStatusCode(200)->
  isForwardedTo('default', 'login')->
  checkResponseElement('body', '/Login Required/i')
;

$b->
  get('/configSecurityIsSecureAction/Index')->
  isStatusCode(200)->
  isForwardedTo('default', 'login')->
  checkResponseElement('body', '/Login Required/i')
;

// settings.yml: max_forwards
$b->
  get('/configSettingsMaxForwards/selfForward')->
  isStatusCode(200)->
  checkResponseElement('body', '/Too many forwards have been detected for this request/i')
;

// filters.yml: add a filter
$b->
  get('/configFiltersSimpleFilter')->
  isStatusCode(200)->
  checkResponseElement('body', '/in a filter/i')->
  checkResponseElement('body', '!/congratulation/i')
;

// css and js inclusions
$b->
  get('/assetInclusion/index')->
  isStatusCode(200)->
  checkResponseElement('head link[rel="stylesheet"]', false)->
  checkResponseElement('head script[type="text/javascript"]', false)
;

// libraries autoloading
$b->
  get('/autoload/index')->
  isStatusCode(200)->
  checkResponseElement('#lib1', 'pong')->
  checkResponseElement('#lib2', 'pong')->
  checkResponseElement('#lib3', 'pong')->
  checkResponseElement('#lib4', 'nopong')
;

// libraries autoloading in a plugin
$b->
  get('/autoloadPlugin/index')->
  isStatusCode(200)->
  checkResponseElement('#lib1', 'pong')->
  checkResponseElement('#lib2', 'pong')->
  checkResponseElement('#lib3', 'pong')
;

// renderText
$b->
  get('/renderText')->
  isStatusCode(200)->
  responseContains('foo')
;

// view.yml when changing template
$b->
  get('/view')->
  isStatusCode(200)->
  checkResponseElement('head title', 'foo title')
;

// getPresentationFor()
$b->
  get('/presentation')->
  isStatusCode(200)->
  checkResponseElement('#foo', 'foo')->
  checkResponseElement('#foo_bis', 'foo')
;
