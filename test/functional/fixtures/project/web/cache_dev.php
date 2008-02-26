<?php

require_once(dirname(__FILE__).'/../lib/cacheConfiguration.class.php');

$configuration = new cacheConfiguration('dev', true);
sfContext::createInstance($configuration)->dispatch();
