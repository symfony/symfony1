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

// filters
$b->
  checkListCustomization('filters', array('filters' => array('title', 'body', 'online', 'category_id', 'created_at')))->
  checkResponseElement('div.sf_admin_filters label[for="title"]', 'Title:')->
  checkResponseElement('div.sf_admin_filters input[name="filters[title]"][id="filters_title"]')->
  checkResponseElement('div.sf_admin_filters label[for="body"]', 'Body:')->
  checkResponseElement('div.sf_admin_filters input[name="filters[body]"][id="filters_body"]')->
  checkResponseElement('div.sf_admin_filters label[for="online"]', 'Online:')->
  checkResponseElement('div.sf_admin_filters select[name="filters[online]"][id="filters_online"] option', 3)->
  checkResponseElement('div.sf_admin_filters label[for="category_id"]', 'Category:')->
  checkResponseElement('div.sf_admin_filters select[name="filters[category_id]"][id="filters_category_id"] option', 3)->
  checkResponseElement('div.sf_admin_filters label[for="created_at"]', 'Created at:')->
  checkResponseElement('div.sf_admin_filters input[name="filters[created_at][from]"][id="filters_created_at_from"]')->
  checkResponseElement('div.sf_admin_filters input[name="filters[created_at][to]"][id="filters_created_at_to"]')
;
