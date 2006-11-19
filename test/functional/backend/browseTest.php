<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'backend';
$fixtures = 'fixtures/fixtures.yml';
$ret = include(dirname(__FILE__).'/../../bootstrap/functional.php');
if (!$ret)
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

// check symfony throws an exception if model class does not exist
$b->
  get('/error')->
  isStatusCode(200)->
  isRequestParameter('module', 'error')->
  isRequestParameter('action', 'index')->
  responseContains('sfInitializationException')->
  responseContains('Unable to scaffold unexistant model')
;

// list page
$b->
  get('/article')->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'index')
;

$content = $b->getResponse()->getContent();

$b->
  getAndCheck('article', 'list')->

  // title
  checkResponseElement('body h1', 'article list')->

  // headers
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_id"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_id"] a[href*="/sort/"]', 'Id')-> // sortable

  checkResponseElement('body table thead tr th[id="sf_admin_list_th_title"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_title"] a[href*="/sort/"]', 'Title')->

  checkResponseElement('body table thead tr th[id="sf_admin_list_th_body"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_body"] a[href*="/sort/"]', 'Body')->

  checkResponseElement('body table thead tr th[id="sf_admin_list_th_online"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_online"] a[href*="/sort/"]', 'Online')->

  checkResponseElement('body table thead tr th[id="sf_admin_list_th_category_id"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_category_id"] a[href*="/sort/"]', 'Category')->

  checkResponseElement('body table thead tr th[id="sf_admin_list_th_created_at"]', true)->
  checkResponseElement('body table thead tr th[id="sf_admin_list_th_created_at"] a[href*="/sort/"]', 'Created at')->

  // first line
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', '1', array('position' => 0))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', 'foo title', array('position' => 1))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', 'bar body', array('position' => 2))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td img', true, array('position' => 3))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', '1', array('position' => 4))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td a[href$="/article/edit/id/1"]', '1')-> // clickable

  // second line
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', '2', array('position' => 0))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', 'foo foo title', array('position' => 1))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', 'bar bar body', array('position' => 2))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td img', false, array('position' => 3))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', '2', array('position' => 4))->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td a[href$="/article/edit/id/2"]', '2')->

  // nb lines
  checkResponseElement('body table tfoot tr th', '/^\s*2 results\s*$/')->

  // buttons
  checkResponseElement('body input[class="sf_admin_action_create"][onclick*="/article/create"]', true)
;

$b->test()->is($b->getResponse()->getContent(), $content, 'article is an alias for article/list');

// sort
$b->
  // asc
  click('Body')->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'list')->

  // parameters
  isRequestParameter('sort', 'body')->
  isRequestParameter('type', 'asc')->

  // check order
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', '2')->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', '1')->

  // check that sorting is stored in session
  getAndCheck('article', 'list')->

  // check order
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', '2')->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', '1')->

  // desc
  click('Body')->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'list')->

  // parameters
  isRequestParameter('sort', 'body')->
  isRequestParameter('type', 'desc')->

  // check order
  checkResponseElement('body table tbody tr[class="sf_admin_row_0"] td', '1')->
  checkResponseElement('body table tbody tr[class="sf_admin_row_1"] td', '2')
;

// edit page
$b->
  click('1')->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  // title
  checkResponseElement('body h1', 'edit article')->

  // parameters
  isRequestParameter('id', 1)->

  // labels
  checkResponseElement('body form#sf_admin_edit_form label[for="article_title"]', 'Title:')->
  checkResponseElement('body form#sf_admin_edit_form label[for="article_body"]', 'Body:')->
  checkResponseElement('body form#sf_admin_edit_form label[for="article_online"]', 'Online:')->
  checkResponseElement('body form#sf_admin_edit_form label[for="article_category_id"]', 'Category:')->
  checkResponseElement('body form#sf_admin_edit_form label[for="article_created_at"]', 'Created at:')->

  // form elements
  checkResponseElement('body form#sf_admin_edit_form input[name="article[title]"][id="article_title"][value="foo title"]')->
  checkResponseElement('body form#sf_admin_edit_form textarea[name="article[body]"][id="article_body"]', 'bar body')->
  checkResponseElement('body form#sf_admin_edit_form input[name="article[online]"][id="article_online"][type="checkbox"][checked="checked"]', true)->
  checkResponseElement('body form#sf_admin_edit_form select[name="article[category_id]"][id="article_category_id"]', true)->
  checkResponseElement('body form#sf_admin_edit_form select[name="article[category_id]"][id="article_category_id"] option[value="1"]', '1')->
  checkResponseElement('body form#sf_admin_edit_form select[name="article[category_id]"][id="article_category_id"] option[value="2"]', '2')->
  checkResponseElement('body form#sf_admin_edit_form input[name="article[created_at]"][id="article_created_at"][value*="-"]')->

  // buttons
  checkResponseElement('body input[class="sf_admin_action_list"][onclick*="/article/list"]', true)->
  checkResponseElement('body input[name="save_and_add"]', true)->
  checkResponseElement('body input[name="save"]', true)->
  checkResponseElement('body input[class="sf_admin_action_delete"][onclick*="confirm"]', true)
;

// save
$b->
  click('save', array('article' => array('title' => 'my title', 'body' => 'my body', 'category_id' => 2)))->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  isRedirected()->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->
  isRequestParameter('id', 1)->

  // check values
  checkResponseElement('input[id="article_title"][value="my title"]')->
  checkResponseElement('#article_body', 'my body')->
  checkResponseElement('input[id="article_online"][checked="checked"]', true)->
  checkResponseElement('#article_category_id option[selected="selected"]', '2')
;

// save and add
$b->
  click('save and add')->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  isRedirected()->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'create')->
  isRequestParameter('id', '')
;

// create
$b->
  getAndCheck('article', 'create')->

  isRequestParameter('id', '')->

  checkResponseElement('body form#sf_admin_edit_form label[for="article_title"]', 'Title:')->
  checkResponseElement('body form#sf_admin_edit_form input[name="article[title]"][id="article_title"][value=""]')->

  click('save', array('article' => array('title' => 'new title', 'body' => 'new body', 'category_id' => 2)))->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  isRedirected()->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->
  isRequestParameter('id', 3)->

  // check values
  checkResponseElement('input[id="article_title"][value="new title"]')->
  checkResponseElement('#article_body', 'new body')->
  checkResponseElement('#article_category_id option[selected="selected"]', '2')->

  // check list
  getAndCheck('article', 'list')->

  // nb lines
  checkResponseElement('body table tfoot tr th', '/^\s*3 results\s*$/')
;

// delete
$b->
  post('/article/delete/id/3')->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'delete')->
  isRedirected()->
  followRedirect()->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'list')->

  // check edit
  get('/article/edit/id/3')->
  isStatusCode(404)->

  // check list
  getAndCheck('article', 'list')->

  // nb lines
  checkResponseElement('body table tfoot tr th', '/^\s*2 results\s*$/')
;

// add some entries to test pagination
$b->get('/article/create');
for ($i = 0; $i < 30; $i++)
{
  $b->click('save and add', array('article' => array('title' => 'title '.$i, 'body' => 'body '.$i)))->followRedirect();
}

$b->
  getAndCheck('article', 'list')->

  // nb lines
  checkResponseElement('body table tfoot tr th', '/32 results/')->

  // check nb pages (2 pages + previous, next, first, last = 5)
  checkResponseElement('body table tfoot tr th a[href*="/article/list/page/"]', 5)->

  // check that links for navigation are ok
  checkResponseElement('body table tfoot tr th a[href*="/article/list/page/1"]', 2)->
  checkResponseElement('body table tfoot tr th a[href*="/article/list/page/2"]', 3)->

  // nb lines on second page
  get('/article/list/page/2')->
  isStatusCode(200)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'list')->

  isRequestParameter('page', 2)->

  checkResponseElement('body table tbody tr', 12)->

  // check that links for navigation are ok
  checkResponseElement('body table tfoot tr th a[href*="/article/list/page/1"]', 3)->
  checkResponseElement('body table tfoot tr th a[href*="/article/list/page/2"]', 2)
;
