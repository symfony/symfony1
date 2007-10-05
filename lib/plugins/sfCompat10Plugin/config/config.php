<?php

class sfCompatAutoload extends sfSimpleAutoload
{
}

if (sfConfig::get('sf_compat_10'))
{
  // autoload classes
  $autoload = sfCompatAutoload::getInstance(sfConfig::get('sf_app_cache_dir').'/sf_compat_autoloader.txt');
  $autoload->addDirectory(dirname(__FILE__).'/../lib');
  $autoload->register();

  // register config handler for validate/*.yml files
  sfConfigCache::getInstance()->registerConfigHandler('modules/*/validate/*.yml', 'sfValidatorConfigHandler');

  // register the validation execution filter
  sfConfig::set('sf_execution_filter', array('sfValidationExecutionFilter', array()));
}
