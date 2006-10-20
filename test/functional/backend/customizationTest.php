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

include(dirname(__FILE__).'/backendTestBrowser.class.php');

$b = new backendTestBrowser();
$b->initialize();

// small customization tests
$b->
  // list
  checkListCustomization('list title customization', array('title' => 'list test title'))->
  checkResponseElement('body h1', 'list test title')->

  // list fields
  checkListCustomization('list field name customization', array('fields' => array('body' => array('name' => 'My Body'))))->
  checkResponseElement('#sf_admin_list_th_body a', 'My Body')->

  // list fields display
  checkListCustomization('list fields display customization', array('display' => array('body', 'title')))->
  checkResponseElement('#sf_admin_list_th_body', true)->
  checkResponseElement('#sf_admin_list_th_title', true)->
  checkResponseElement('#sf_admin_list_th_id', false)->
  checkResponseElement('#sf_admin_list_th_category_id', false)->
  checkResponseElement('#sf_admin_list_th_created_at', false)->

  // list buttons
  checkListCustomization('remove create button', array('actions' => array()))->
  checkResponseElement('body input[class="sf_admin_action_create"][onclick*="/article/create"]', false)->

  checkListCustomization('add custom button', array('actions' => array('_create' => null, 'custom' => array('name' => 'my button', 'action' => 'myAction', 'params' => 'class=myButtonClass'))))->
  checkResponseElement('body input[class="sf_admin_action_create"][onclick*="/article/create"]', true)->
  checkResponseElement('body input[class="myButtonClass"][onclick*="/article/myAction"][value="my button"]', true)->

  checkListCustomization('add custom button without create', array('actions' => array('custom' => array('name' => 'my button', 'action' => 'myAction', 'params' => 'class=myButtonClass'))))->
  checkResponseElement('body input[class="sf_admin_action_create"][onclick*="/article/create"]', false)->
  checkResponseElement('body input[class="myButtonClass"][onclick*="/article/myAction"][value="my button"]', true)->

  // edit
  checkEditCustomization('edit title customization', array('title' => 'edit test title'))->
  checkResponseElement('body h1', 'edit test title')->

  checkEditCustomization('edit title customization', array('title' => 'edit "%%title%%"'))->
  checkResponseElement('body h1', 'edit "foo title"')->

  // edit fields
  checkEditCustomization('edit field name customization', array('fields' => array('body' => array('name' => 'My Body'))))->
  checkResponseElement('label[for="article_body"]', 'My Body:')->

  // edit fields display
  checkEditCustomization('edit fields display customization', array('display' => array('body', 'title')))->
  checkResponseElement('label[for="article_body"]', true)->
  checkResponseElement('label[for="article_title"]', true)->
  checkResponseElement('label[for="article_id"]', false)->
  checkResponseElement('label[for="article_category_id"]', false)->
  checkResponseElement('label[for="article_created_at"]', false)->

  checkEditCustomization('edit fields display customization', array('display' => array('NONE' => array('body'), 'Another' => array('title'))))->
  checkResponseElement('label[for="article_body"]', true)->
  checkResponseElement('label[for="article_title"]', true)->
  checkResponseElement('label[for="article_id"]', false)->
  checkResponseElement('label[for="article_category_id"]', false)->
  checkResponseElement('label[for="article_created_at"]', false)->
  checkResponseElement('fieldset#sf_fieldset_none', true)->
  checkResponseElement('fieldset#sf_fieldset_none + h2', false)->
  checkResponseElement('fieldset#sf_fieldset_none textarea[name="article[body]"]', true)->
  checkResponseElement('fieldset#sf_fieldset_another', true)->
  checkResponseElement('fieldset#sf_fieldset_another + h2', 'Another')->
  checkResponseElement('fieldset#sf_fieldset_another input[name="article[title]"]', true)->

  // edit buttons
  checkEditCustomization('remove save button', array('actions' => array()))->
  checkResponseElement('body input[class="sf_admin_action_list"][onclick*="/article/list"]', false)->
  checkResponseElement('body input[name="save_and_add"]', false)->
  checkResponseElement('body input[name="save"]', false)->
  checkResponseElement('body input[class="sf_admin_action_delete"][onclick*="confirm"]', false)->

  checkEditCustomization('remove save button', array('actions' => array('_save' => null)))->
  checkResponseElement('body input[name="save_and_add"]', false)->
  checkResponseElement('body input[name="save"]', true)->
  checkResponseElement('body input[class="sf_admin_action_delete"][onclick*="confirm"]', false)->

  checkEditCustomization('add custom button', array('actions' => array('_save' => null, 'custom' => array('name' => 'my button', 'action' => 'myAction', 'params' => 'class=myButtonClass'))))->
  checkResponseElement('body input[name="save"]', true)->
  checkResponseElement('body input[class="myButtonClass"][onclick*="/article/myAction/id/1"][value="my button"]', true)->

  checkEditCustomization('add custom button without save', array('actions' => array('custom' => array('name' => 'my button', 'action' => 'myAction', 'params' => 'class=myButtonClass'))))->
  checkResponseElement('body input[name="save"]', false)->
  checkResponseElement('body input[class="myButtonClass"][onclick*="/article/myAction/id/1"][value="my button"]', true)->

  checkEditCustomization('rename save button', array('actions' => array('save' => array('name' => 'My save'))))->
  checkResponseElement('body input[value="My save"]', true)
;
