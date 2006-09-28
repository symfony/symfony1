<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'crud';
$fixtures = 'fixtures/fixtures.yml';

include(dirname(__FILE__).'/bootstrap.php');

$b = new sfTestBrowser();
$b->initialize();

// check symfony throws an exception if model class does not exist
$b->
  get('/error')->
  isRequestParameter('module', 'error')->
  isRequestParameter('action', 'index')->
  responseContains('sfInitializationException')->
  responseContains('Unable to scaffold unexistant model')
;

// list page
$b->
  get('/simple')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'index')
;

$content = $b->getResponse()->getContent();

$b->
  get('/simple/list')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'list')->
  checkResponseElement('body h1', 'simple')->
  checkResponseElement('body table thead tr th', '/^(Title|Body|Id|Category Id|Created at)$/')->
  checkResponseElement('body table tbody tr td', 'foo title', array('position' => 1))->
  checkResponseElement('body table tbody tr td', 'bar body', array('position' => 2))->
  checkResponseElement('body table tbody tr td', '1', array('position' => 3))->
  checkResponseElement('a[href$="/simple/create"]', 'create')->
  checkResponseElement('a[href*="/simple/show/id/"]', '/\d+/', array('count' => 2))
;

$b->test()->is($b->getResponse()->getContent(), $content, 'simple is an alias for simple/list');

// show page
$b->
  click('1')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'show')->
  isRequestParameter('id', 1)->
  checkResponseElement('a[href$="/simple/edit/id/1"]', 'edit')->
  checkResponseElement('a[href$="/simple/list"]', 'list')->
  checkResponseElement('body table tbody tr', '/Id\:\s+1/', array('position' => 0))->
  checkResponseElement('body table tbody tr', '/Title\:\s+foo title/', array('position' => 1))->
  checkResponseElement('body table tbody tr', '/Body\:\s+bar body/', array('position' => 2))->
  checkResponseElement('body table tbody tr', '/Category\:\s+1/', array('position' => 3))->
  checkResponseElement('body table tbody tr', '/Created at\:\s+[0-9\-\:\s]+/', array('position' => 4))
;

// edit page
$b->
  click('edit')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'edit')->
  isRequestParameter('id', 1)->
  checkResponseElement('a[href$="/simple/show/id/1"]', 'cancel')->
  checkResponseElement('a[href$="/simple/delete/id/1"]', 'delete')->
  checkResponseElement('a[href$="/simple/delete/id/1"][onclick*="confirm"]')->
  checkResponseElement('body table tbody th', 'Title:', array('position' => 0))->
  checkResponseElement('body table tbody th', 'Body:', array('position' => 1))->
  checkResponseElement('body table tbody th', 3)->
  checkResponseElement('body table tbody td', 3)->
  checkResponseElement('body table tbody td input[id="title"][name="title"][value*="title"]')->
  checkResponseElement('body table tbody td textarea[id="body"][name="body"]', 'bar body')->
  checkResponseElement('body table tbody td select[id="category_id"][name="category_id"]', true)->
  checkResponseElement('body table tbody td select[id="category_id"][name="category_id"] option[value="1"]', '1')->
  checkResponseElement('body table tbody td select[id="category_id"][name="category_id"] option[value="2"]', '2')
;

// create page
$b->
  get('/simple/create')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'create')->
  isRequestParameter('id', null)->
  checkResponseElement('a[href$="/simple/list"]', 'cancel')->
  checkResponseElement('body table tbody th', 'Title:', array('position' => 0))->
  checkResponseElement('body table tbody th', 'Body:', array('position' => 1))->
  checkResponseElement('body table tbody th', 3)->
  checkResponseElement('body table tbody td', 3)->
  checkResponseElement('body table tbody td input[id="title"][name="title"][value=""]')->
  checkResponseElement('body table tbody td textarea[id="body"][name="body"]', '')
;

// save
$b->
  click('save', array('title' => 'my title', 'body' => 'my body', 'category_id' => 2))->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'update')->
  isRedirected()
;

$b->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'show')->
  isRequestParameter('id', 3)->
  checkResponseElement('a[href$="/simple/edit/id/3"]', 'edit')->
  checkResponseElement('a[href$="/simple/list"]', 'list')->
  checkResponseElement('body table tbody tr', '/Id\:\s+3/', array('position' => 0))->
  checkResponseElement('body table tbody tr', '/Title\:\s+my title/', array('position' => 1))->
  checkResponseElement('body table tbody tr', '/Body\:\s+my body/', array('position' => 2))->
  checkResponseElement('body table tbody tr', '/Category\:\s+2/', array('position' => 3))->
  checkResponseElement('body table tbody tr', '/Created at\:\s+[0-9\-\:\s]+/', array('position' => 4))
;

$b->
  click('list')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'list')
;

// delete
$b->
  get('/simple/edit/id/3')->

  click('delete')->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'delete')->
  isRedirected()->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'simple')->
  isRequestParameter('action', 'list')->

  get('/simple/edit/id/3')->
  isStatusCode(404)
;
