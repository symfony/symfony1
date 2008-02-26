<?php

require_once(dirname(__FILE__).'/../lib/backendConfiguration.class.php');

$configuration = new backendConfiguration('prod', false);
sfContext::createInstance($configuration)->dispatch();
