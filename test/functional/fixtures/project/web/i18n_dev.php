<?php

require_once(dirname(__FILE__).'/../lib/i18nConfiguration.class.php');

$configuration = new i18nConfiguration('dev', true);
sfContext::createInstance($configuration)->dispatch();
