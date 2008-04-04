<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
$fixtures = 'fixtures/fixtures.yml';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// en
$b->
  get('/i18n/default')->
  isStatusCode(200)->
  isRequestParameter('module', 'i18n')->
  isRequestParameter('action', 'default')->
  checkResponseElement('#movies .default:first', '')->
  checkResponseElement('#movies .it:first', 'La Vita è bella')->
  checkResponseElement('#movies .fr:first', 'La Vie est belle')
;

// fr
$b->
  get('/i18n/index')->
  isStatusCode(200)->
  isRequestParameter('module', 'i18n')->
  isRequestParameter('action', 'index')->
  checkResponseElement('#movies .default:first', 'La Vie est belle')->
  checkResponseElement('#movies .it:first', 'La Vita è bella')->
  checkResponseElement('#movies .fr:first', 'La Vie est belle')
;

// still fr
$b->
  get('/i18n/default')->
  isStatusCode(200)->
  isRequestParameter('module', 'i18n')->
  isRequestParameter('action', 'default')->
  checkResponseElement('#movies .default:first', 'La Vie est belle')->
  checkResponseElement('#movies .it:first', 'La Vita è bella')->
  checkResponseElement('#movies .fr:first', 'La Vie est belle')
;
