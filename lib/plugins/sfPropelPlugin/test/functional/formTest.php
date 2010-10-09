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

// file upload
$fileToUpload = dirname(__FILE__).'/fixtures/config/databases.yml';
$uploadedFile = sfConfig::get('sf_cache_dir').'/uploaded.yml';
$name = 'test';
$b->
  get('/attachment/index')->
  isRequestParameter('module', 'attachment')->
  isRequestParameter('action', 'index')->
  isStatusCode(200)->
  click('submit', array('attachment' => array('name' => $name, 'file' => $fileToUpload)))->
  isRedirected()->
  followRedirect()->
  responseContains('ok')
;

$b->test()->ok(file_exists($uploadedFile), 'file is uploaded');
$b->test()->is(file_get_contents($uploadedFile), file_get_contents($fileToUpload), 'file is correctly uploaded');

$c = new Criteria();
$c->add(AttachmentPeer::NAME, $name);
$attachments = AttachmentPeer::doSelect($c);

$b->test()->is(count($attachments), 1, 'the attachment has been saved in the database');
$b->test()->is($attachments[0]->getFile(), $uploadedFile, 'the attachment filename has been saved in the database');

// sfValidatorPropelUnique

// create a category with a unique name
$b->
  get('/unique/category')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'category')->
  isStatusCode(200)->
  click('submit', array('category' => array('name' => 'foo')))->
  isRedirected()->
  followRedirect()->
  responseContains('ok')
;

// create another category with the same name
// we must have an error
$b->
  get('/unique/category')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'category')->
  isStatusCode(200)->
  click('submit', array('category' => array('name' => 'foo')))->
  checkResponseElement('td[colspan="2"] .error_list li', 0)->
  checkResponseElement('.error_list li', 'An object with the same "name" already exist.')->
  checkResponseElement('.error_list li', 1)
;

// same thing but with a global error
$b->
  get('/unique/category')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'category')->
  isStatusCode(200)->
  click('submit', array('category' => array('name' => 'foo'), 'global' => 1))->
  checkResponseElement('td[colspan="2"] .error_list li', 'An object with the same "name" already exist.')->
  checkResponseElement('td[colspan="2"] .error_list li', 1)
;

// updating the same category again with the same name is allowed
$b->
  get('/unique/category?category[id]='.CategoryPeer::getByName('foo')->getId())->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'category')->
  isStatusCode(200)->
  click('submit')->
  isRedirected()->
  followRedirect()->
  responseContains('ok')
;

// create an article with a unique title-category_id
$b->
  get('/unique/article')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'article')->
  isStatusCode(200)->
  click('submit', array('article' => array('title' => 'foo', 'category_id' => 1)))->
  isRedirected()->
  followRedirect()->
  responseContains('ok')
;

// create another article with the same title but a different category_id
$b->
  get('/unique/article')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'article')->
  isStatusCode(200)->
  click('submit', array('article' => array('title' => 'foo', 'category_id' => 2)))->
  isRedirected()->
  followRedirect()->
  responseContains('ok')
;

// create another article with the same title and category_id as the first one
// we must have an error
$b->
  get('/unique/article')->
  isRequestParameter('module', 'unique')->
  isRequestParameter('action', 'article')->
  isStatusCode(200)->
  click('submit', array('article' => array('title' => 'foo', 'category_id' => 1)))->
  checkResponseElement('.error_list li', 'An object with the same "title, category_id" already exist.')
;

// sfValidatorPropelChoice

// submit a form with an impossible choice validator
$b->
  get('/choice/article')->
  isRequestParameter('module', 'choice')->
  isRequestParameter('action', 'article')->
  isStatusCode(200)->
  click('submit', array('article' => array('title' => 'foobar', 'category_id' => 1, 'author_article_list' => array(1)), 'impossible_validator' => 1))->
  checkResponseElement('.error_list li', 'Invalid category.')->
  checkResponseElement('.error_list li', 1)
;

// sfValidatorPropelChoiceMany

// submit a form with an impossible choice validator
$b->
  get('/choice/article')->
  isRequestParameter('module', 'choice')->
  isRequestParameter('action', 'article')->
  isStatusCode(200)->
  click('submit', array('article' => array('title' => 'foobar', 'category_id' => 1, 'author_article_list' => array(1)), 'impossible_validator_many' => 1))->
  checkResponseElement('.error_list li', 'Invalid author.')->
  checkResponseElement('.error_list li', 1)
;
