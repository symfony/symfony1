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
