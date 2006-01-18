<?php

class simpleTest extends sfLiveProjectUnitTestCase
{
  public function test_simple()
  {
    // check symfony default module response on a new project
    $this->checkModuleResponse('/', '/congratulations/i');

    $this->runSymfony('init-module app module');
    $this->checkModuleResponse('/module/index', '/congratulation/i');
  }

  public function test_nonExistantModule()
  {
    $this->checkModuleResponse('/nonexistantmodule/index', '/404 Error/i');
  }

  // settings.yml: available
  public function test_configSettingsAvailable()
  {
    sfConfig::set('sf_available', false);

    $this->checkModuleResponse('/module', '/unavailable/i', '/congratulation/i');

    sfConfig::set('sf_available', true);
  }

  // module.yml: enabled
  public function test_configModuleEnabled()
  {
    sfConfig::set('mod_module_enabled', false);

    $this->checkModuleResponse('/module', '/module is unavailable/i', '/congratulation/i');

    sfConfig::set('mod_module_enabled', true);
  }

  // view.yml: has_layout
  public function test_configViewHasLayout()
  {
    $this->initModule('configViewHasLayout');

    $this->checkModuleResponse('/configViewHasLayout/withoutLayout', '/no layout/i', '/<html>/i');
  }

  // security.yml: is_secure
  public function test_configSecurityIsSecure()
  {
    $this->initModule('configSecurityIsSecure');

    $this->checkModuleResponse('/configSecurityIsSecure/index', '/You must enter you credential to access this page/i');
  }

  // module.yml: is_internal
  public function test_configModuleIsInternal()
  {
    $this->initModule('configModuleIsInternal');

    $this->checkModuleResponse('/configModuleIsInternal/index', '/cannot be called directly/i');
  }

  // settings.yml: max_forwards
  public function test_configSettingsMaxForwards()
  {
    $this->initModule('configSettingsMaxForwards');

    $this->checkModuleResponse('/configSettingsMaxForwards/selfForward', '/Too many forwards have been detected for this request/i');
  }

  // filters.yml: add a filter
  public function test_configFiltersSimpleFilter()
  {
    $this->initModule('configFiltersSimpleFilter');

    $this->checkModuleResponse('/configFiltersSimpleFilter', array('/in a filter/i', '/congratulation/i'));
  }

  public function getFixturesDir()
  {
    return dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures';
  }
}

?>