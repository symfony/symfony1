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

$b = new sfTestBrowser();
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
