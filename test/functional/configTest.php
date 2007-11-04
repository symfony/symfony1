<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();
$b->get('/');

$t = $b->test();

// simple configuration files
$t->diag('sfLoader::getConfigDirs()');
$t->is(get_config_dirs('config/filters.yml'), array(
  'SYMFONY/config/filters.yml',
  'PROJECT/plugins/sfConfigPlugin/config/filters.yml',
  'PROJECT/config/filters.yml',
  'PROJECT/apps/frontend/config/filters.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

// configuration files for modules
$t->is(get_config_dirs('modules/sfConfigPlugin/config/view.yml'), array(
  'SYMFONY/config/view.yml',
  'PROJECT/plugins/sfConfigPlugin/config/view.yml',
  'PROJECT/config/view.yml',
  'PROJECT/apps/frontend/config/view.yml',
  'PROJECT/plugins/sfConfigPlugin/modules/sfConfigPlugin/config/view.yml',
  'PROJECT/apps/frontend/modules/sfConfigPlugin/config/view.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

// nested configuration files
$t->is(get_config_dirs('config/dirmyconfig/myconfig.yml'), array(
  'PROJECT/config/dirmyconfig/myconfig.yml',
  'PROJECT/plugins/sfConfigPlugin/config/dirmyconfig/myconfig.yml',
  'PROJECT/apps/frontend/config/dirmyconfig/myconfig.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

function get_config_dirs($configPath)
{
  $dirs = array();
  foreach (sfLoader::getConfigPaths($configPath) as $dir)
  {
    $dirs[] = $dir;
  }

  return array_map('strip_paths', $dirs);
}

function strip_paths($f)
{
  $f = str_replace(
    array(sfConfig::get('sf_symfony_data_dir'), sfConfig::get('sf_root_dir'), DIRECTORY_SEPARATOR),
    array('SYMFONY', 'PROJECT', '/'),
    $f);

  return $f;
}
