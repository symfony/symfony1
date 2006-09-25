<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'cache';

include(dirname(__FILE__).'/bootstrap.php');

class myTestBrowser extends sfTestBrowser
{
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
