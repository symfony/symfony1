<?php

$sf_root_dir    = sfConfig::get('sf_root_dir');
$sf_app         = sfConfig::get('sf_app');
$sf_environment = sfConfig::get('sf_environment');

sfConfig::add(array(
  // root directory structure
  'sf_cache_dir_name'   => 'cache',
  'sf_log_dir_name'     => 'log',
  'sf_lib_dir_name'     => 'lib',
  'sf_model_dir_name'   => 'model',
  'sf_web_dir_name'     => 'web',
  'sf_data_dir_name'    => 'data',
  'sf_config_dir_name'  => 'config',
  'sf_apps_dir_name'    => 'apps',

  // global directory structure
  'sf_app_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app,
  'sf_model_dir'      => $sf_root_dir.DIRECTORY_SEPARATOR.'model',
  'sf_lib_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.'lib',
  'sf_web_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.'web',
  'sf_upload_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'uploads',
  'sf_base_cache_dir' => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app,
  'sf_cache_dir'      => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment,
  'sf_log_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.'log',
  'sf_data_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.'data',
  'sf_config_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.'config',

  // SF_CACHE_DIR directory structure
  'sf_template_cache_dir' => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment.DIRECTORY_SEPARATOR.'template',
  'sf_i18n_cache_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment.DIRECTORY_SEPARATOR.'i18n',
  'sf_config_cache_dir'   => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment.DIRECTORY_SEPARATOR.'config',
  'sf_test_cache_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment.DIRECTORY_SEPARATOR.'test',
  'sf_module_cache_dir'   => $sf_root_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.$sf_environment.DIRECTORY_SEPARATOR.'module',

  // SF_APP_DIR sub-directories names
  'sf_app_i18n_dir_name'     => 'i18n',
  'sf_app_config_dir_name'   => 'config',
  'sf_app_lib_dir_name'      => 'lib',
  'sf_app_module_dir_name'   => 'modules',
  'sf_app_template_dir_name' => 'templates',

  // SF_APP_DIR directory structure
  'sf_app_config_dir'   => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.'config',
  'sf_app_lib_dir'      => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.'lib',
  'sf_app_module_dir'   => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.'modules',
  'sf_app_template_dir' => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.'templates',
  'sf_app_i18n_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$sf_app.DIRECTORY_SEPARATOR.'i18n',

  // SF_APP_MODULE_DIR sub-directories names
  'sf_app_module_action_dir_name'   => 'actions',
  'sf_app_module_template_dir_name' => 'templates',
  'sf_app_module_lib_dir_name'      => 'lib',
  'sf_app_module_view_dir_name'     => 'views',
  'sf_app_module_validate_dir_name' => 'validate',
  'sf_app_module_config_dir_name'   => 'config',
  'sf_app_module_i18n_dir_name'   => 'i18n',
));

?>