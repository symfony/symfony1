<?php

require_once(dirname(__FILE__).'/../lib/crudConfiguration.class.php');

$configuration = new crudConfiguration('dev', true);
sfContext::createInstance($configuration)->dispatch();
