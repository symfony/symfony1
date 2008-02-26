<?php

require_once(dirname(__FILE__).'/../lib/backendConfiguration.class.php');

$configuration = new backendConfiguration('dev', true);
sfContext::createInstance($configuration)->dispatch();
