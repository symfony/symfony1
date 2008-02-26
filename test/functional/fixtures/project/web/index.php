<?php

require_once(dirname(__FILE__).'/../lib/frontendConfiguration.class.php');

$configuration = new frontendConfiguration('prod', false);
sfContext::createInstance($configuration)->dispatch();
