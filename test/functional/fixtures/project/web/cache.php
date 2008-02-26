<?php

require_once(dirname(__FILE__).'/../lib/cacheConfiguration.class.php');

$configuration = new cacheConfiguration('prod', false);
sfContext::createInstance($configuration)->dispatch();
