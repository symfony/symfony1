<?php

require_once(dirname(__FILE__).'/../lib/crudConfiguration.class.php');

$configuration = new crudConfiguration('prod', false);
sfContext::createInstance($configuration)->dispatch();
