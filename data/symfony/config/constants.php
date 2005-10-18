<?php

if (!defined('DS'))
{
  define('DS', DIRECTORY_SEPARATOR);
}

// root directory structure
define('SF_CACHE_DIR_NAME', 'cache');
define('SF_LOG_DIR_NAME',   'log');
define('SF_LIB_DIR_NAME',   'lib');
define('SF_MODEL_DIR_NAME', 'model');
define('SF_WEB_DIR_NAME',   'web');
define('SF_DATA_DIR_NAME',  'data');

// global directory structure
define('SF_APP_DIR',        SF_ROOT_DIR.DS.SF_APP);
define('SF_MODEL_DIR',      SF_ROOT_DIR.DS.SF_MODEL_DIR_NAME);
define('SF_LIB_DIR',        SF_ROOT_DIR.DS.SF_LIB_DIR_NAME);
define('SF_WEB_DIR',        SF_ROOT_DIR.DS.SF_WEB_DIR_NAME);
define('SF_UPLOAD_DIR',     SF_WEB_DIR.DS.'uploads');
define('SF_BASE_CACHE_DIR', SF_ROOT_DIR.DS.SF_CACHE_DIR_NAME.DS.SF_APP);
define('SF_CACHE_DIR',      SF_BASE_CACHE_DIR.DS.SF_ENVIRONMENT);
define('SF_LOG_DIR',        SF_ROOT_DIR.DS.SF_LOG_DIR_NAME);
define('SF_DATA_DIR',       SF_ROOT_DIR.DS.SF_DATA_DIR_NAME);

// SF_CACHE_DIR directory structure
define('SF_TEMPLATE_CACHE_DIR', SF_CACHE_DIR.DS.'template');
define('SF_I18N_CACHE_DIR',     SF_CACHE_DIR.DS.'i18n');
define('SF_CONFIG_CACHE_DIR',   SF_CACHE_DIR.DS.'config');
define('SF_TEST_CACHE_DIR',     SF_CACHE_DIR.DS.'test');
define('SF_MODULE_CACHE_DIR',   SF_CACHE_DIR.DS.'module');

// SF_APP_DIR sub-directories names
define('SF_APP_I18N_DIR_NAME',     'i18n');
define('SF_APP_CONFIG_DIR_NAME',   'config');
define('SF_APP_LIB_DIR_NAME',      'lib');
define('SF_APP_MODULE_DIR_NAME',   'modules');
define('SF_APP_TEMPLATE_DIR_NAME', 'templates');

// SF_APP_DIR directory structure
define('SF_APP_CONFIG_DIR',   SF_APP_DIR.DS.SF_APP_CONFIG_DIR_NAME);
define('SF_APP_LIB_DIR',      SF_APP_DIR.DS.SF_APP_LIB_DIR_NAME);
define('SF_APP_MODULE_DIR',   SF_APP_DIR.DS.SF_APP_MODULE_DIR_NAME);
define('SF_APP_TEMPLATE_DIR', SF_APP_DIR.DS.SF_APP_TEMPLATE_DIR_NAME);
define('SF_APP_I18N_DIR',     SF_APP_DIR.DS.SF_APP_I18N_DIR_NAME);

// SF_APP_MODULE_DIR sub-directories names
define('SF_APP_MODULE_ACTION_DIR_NAME',   'actions');
define('SF_APP_MODULE_TEMPLATE_DIR_NAME', 'templates');
define('SF_APP_MODULE_LIB_DIR_NAME',      'lib');
define('SF_APP_MODULE_VIEW_DIR_NAME',     'views');
define('SF_APP_MODULE_VALIDATE_DIR_NAME', 'validate');
define('SF_APP_MODULE_CONFIG_DIR_NAME',   'config');

?>