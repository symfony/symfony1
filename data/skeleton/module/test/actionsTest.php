<?php

// create a new test browser
$browser = new sfTestBrowser();
$browser->initialize();

$browser->
  get('/##MODULE_NAME##/index')->
  isStatusCode(200)->
  isRequestParameter('module', '##MODULE_NAME##')->
  isRequestParameter('action', 'index')->
  checkResponseElement('body', '/##MODULE_NAME##/')
;
