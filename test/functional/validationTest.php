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

$b->
  get('/validation')->
  isStatusCode(200)->
  isRequestParameter('module', 'validation')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body h1', 'Form validation tests')->
  checkResponseElement('body form input[name="fake"][value=""]')->
  checkResponseElement('body form input[name="id"][value="1"]')->
  checkResponseElement('body form input[name="article[title]"][value="title"]')->
  checkResponseElement('body form textarea[name="article[body]"]', 'body')->
  checkResponseElement('body ul[class="errors"] li', 0)
;

// test fill in filter
$b->
  click('submit')->
  isStatusCode(200)->
  isRequestParameter('module', 'validation')->
  isRequestParameter('action', 'index')->

  checkResponseElement('body form input[name="fake"][value=""]')->
  checkResponseElement('body form input[name="id"][value="1"]')->
  checkResponseElement('body form input[name="password"][value=""]')->
  checkResponseElement('body form input[name="article[title]"][value="title"]')->
  checkResponseElement('body form textarea[name="article[body]"]', 'body')->

  checkResponseElement('body ul[class="errors"] li[class="fake"]')
;

$b->
  click('submit', array('article' => array('title' => 'my title', 'body' => 'my body', 'password' => 'test', 'id' => 4)))->
  isStatusCode(200)->
  isRequestParameter('module', 'validation')->
  isRequestParameter('action', 'index')->

  checkResponseElement('body form input[name="fake"][value=""]')->
  checkResponseElement('body form input[name="id"][value="1"]')->
  checkResponseElement('body form input[name="password"][value=""]')->
  checkResponseElement('body form input[name="article[title]"][value="my title"]')->
  checkResponseElement('body form textarea[name="article[body]"]', 'my body')->

  checkResponseElement('body ul[class="errors"] li[class="fake"]')
;
