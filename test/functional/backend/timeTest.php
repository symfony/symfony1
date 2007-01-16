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
if (!include(dirname(__FILE__).'/../../bootstrap/functional.php'))
{
  return;
}

include(dirname(__FILE__).'/backendTestBrowser.class.php');

$b = new backendTestBrowser();
$b->initialize();

$b->
  post('/article/edit/id/1', array('article' => array('end_date' => 'not a date')))->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  isRedirected(true)->
  followRedirect()->
  checkResponseElement('input[name="article[end_date]"][value=""]')
;

// non rich date
$tomorrow = time() + 86400;
$b->
  customizeGenerator(array('edit' => array('fields' => array('end_date' => array('params' => 'rich=false')))))->

  post('/article/edit/id/1', array('article' => array('end_date' => array('day' => date('d', $tomorrow), 'month' => date('m', $tomorrow), 'year' => date('Y', $tomorrow)))))->
  isStatusCode(302)->
  isRequestParameter('module', 'article')->
  isRequestParameter('action', 'edit')->

  isRedirected(true)->
  followRedirect()->
  checkResponseElement(sprintf('select[name="article[end_date][day]"] option[value="%s"][selected="selected"]', date('d', $tomorrow)))->
  checkResponseElement(sprintf('select[name="article[end_date][month]"] option[value="%s"][selected="selected"]', date('m', $tomorrow)))->
  checkResponseElement(sprintf('select[name="article[end_date][year]"] option[value="%s"][selected="selected"]', date('Y', $tomorrow)))
;
