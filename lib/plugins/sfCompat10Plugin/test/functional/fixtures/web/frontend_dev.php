<?php

require_once(dirname(__FILE__).'/../lib/frontendConfiguration.class.php');

$configuration = new frontendConfiguration('dev', true);
sfContext::createInstance($configuration)->dispatch();
