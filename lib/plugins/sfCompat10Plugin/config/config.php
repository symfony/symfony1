<?php

if (sfConfig::get('sf_compat_10'))
{
  if (!class_exists('sfCompatAutoload', false))
  {
    class sfCompatAutoload extends sfSimpleAutoload
    {

    }

    // autoload classes
    $autoload = sfCompatAutoload::getInstance(sfConfig::get('sf_app_cache_dir').'/sf_compat_autoloader.txt');
    $autoload->addDirectory(dirname(__FILE__).'/../lib');
    $autoload->register();
  }

  // register config handler for validate/*.yml files
  sfProjectConfiguration::getActive()->getConfigCache()->registerConfigHandler('modules/*/validate/*.yml', 'sfValidatorConfigHandler');

  // register config handler for config/mailer.yml files
  sfProjectConfiguration::getActive()->getConfigCache()->registerConfigHandler('modules/*/config/mailer.yml', 'sfDefineEnvironmentConfigHandler', array('prefix' => 'sf_mailer_', 'module' => 'yes'));

  // register the validation execution filter
  sfConfig::set('sf_execution_filter', array('sfValidationExecutionFilter', array()));
}
