<?php

require_once(dirname(__FILE__).'/../lib/##APP_NAME##Configuration.class.php');

$configuration = new ##APP_NAME##Configuration('##ENVIRONMENT##', ##IS_DEBUG##);
sfContext::createInstance($configuration)->dispatch();
