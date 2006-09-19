<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'crud';
$fixtures = array(
  'Article' => array(
    'article_1' => array(
      'title'      => 'foo title',
      'body'       => 'bar body',
      'created_at' => time(),
    ),
    'article_2'    => array(
      'title'      => 'foo foo title',
      'body'       => 'bar bar body',
      'created_at' => time(),
    ),
  ),
);

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
  checkResponseElement('body table thead tr th', '/^(Title|Body|Id|Created at)$/')->
  checkResponseElement('body table tbody tr td', 'foo title', array('position' => 1))->
  checkResponseElement('body table tbody tr td', 'bar body', array('position' => 2))->
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
  checkResponseElement('body table tbody tr', '/Created at\:\s+[0-9\-\:\s]+/', array('position' => 3))
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
  checkResponseElement('body table tbody th', 'Title:', array('position' => 0))->
  checkResponseElement('body table tbody th', 'Body:', array('position' => 1))->
  checkResponseElement('body table tbody th', 2)->
  checkResponseElement('body table tbody td', 2)->
  checkResponseElement('body table tbody td input[id="title"]')->
  checkResponseElement('body table tbody td textarea[id="body"]')
;
