<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pake_desc('upgrade to a new symfony release');
pake_task('upgrade', 'project_exists');

pake_desc('downgrade to a previous symfony release');
pake_task('downgrade', 'project_exists');

function run_downgrade($task, $args)
{
  throw new Exception('I have no downgrade script for this release.');
}

function run_upgrade($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the upgrade script to use (0.8 to upgrade to 0.8 for example).');
  }

  $version = $args[0];

   if ($version == '0.8')
   {
     run_upgrade_0_8($task, $args);
   }
   else
   {
     throw new Exception('I have no upgrade script for this release.');
   }
}

function run_upgrade_0_8($task, $args)
{
  // upgrade propel.ini
  _upgrade_0_8_propel_ini();

  // upgrade model classes
  _upgrade_0_8_propel_model();

  // find all applications for this project
  $apps = pakeFinder::type('directory')->name(sfConfig::get('sf_app_module_dir_name'))->mindepth(1)->maxdepth(1)->relative()->in(sfConfig::get('sf_apps_dir_name'));

  // update schemas
  _upgrade_0_8_schemas();

  // upgrade all applications
  foreach ($apps as $app_module_dir)
  {
    $app = str_replace(DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name'), '', $app_module_dir);
    pake_echo_action('upgrade 0.8', sprintf('[upgrading application "%s"]', $app));

    $app_dir = sfConfig::get('sf_apps_dir_name').'/'.$app;

    // upgrade config.php
    _upgrade_0_8_config_php($app_dir);

    // upgrade all modules
    $dir = $app_dir.'/'.sfConfig::get('sf_app_module_dir_name');
    if ($dir)
    {
      // template dirs
      $template_dirs   = pakeFinder::type('directory')->name('templates')->mindepth(1)->maxdepth(1)->in($dir);
      $template_dirs[] = $app_dir.'/'.sfConfig::get('sf_app_template_dir_name');

      _upgrade_0_8_deprecated_for_templates($template_dirs);

      _upgrade_0_8_deprecated_for_generator($app_dir);

      _upgrade_0_8_cache_yml($app_dir);

      // actions dirs
      $action_dirs = pakeFinder::type('directory')->name('actions')->mindepth(1)->maxdepth(1)->in($dir);

      _upgrade_0_8_deprecated_for_actions($action_dirs);

      // view.yml
      _upgrade_0_8_view_yml($app_dir);

      _upgrade_0_8_php_files($app_dir);
    }
  }

  pake_echo_action('upgrade 0.8', 'done');

  pake_mkdirs(sfConfig::get('sf_root_dir').'/plugins');
  if (is_dir(sfConfig::get('sf_lib_dir').'/plugins'))
  {
    pake_echo_comment('WARNING: you must re-install all your plugins');
  }

  pake_echo_comment('you can now:');
  pake_echo_comment(' - rebuild model: symfony propel-build-model');
  pake_echo_comment(' - clear cache: symfony cc');
}

function _upgrade_0_8_php_files($app_dir)
{
  pake_echo_action('upgrade 0.8', 'upgrading sf/ path configuration');

  $php_files = pakeFinder::type('file')->name('*.php')->in($app_dir);
  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    $deprecated = array(
      "'/sf/js/prototype"     => "sfConfig::get('sf_prototype_web_dir').'/js",
      "'/sf/css/prototype"    => "sfConfig::get('sf_prototype_web_dir').'/css",
      "'/sf/js/sf_admin"      => "sfConfig::get('sf_prototype_web_dir').'/js",
      "'/sf/css/sf_admin"     => "sfConfig::get('sf_prototype_web_dir').'/css",
      "'/sf/images/sf_admin"  => "sfConfig::get('sf_prototype_web_dir').'/images",
    );
    $seen = array();
    foreach ($deprecated as $old => $new)
    {
      $count = 0;
      $content = str_replace($old, $new, $content, $count);
      if ($count && !isset($seen[$old]))
      {
        $seen[$old] = true;
        pake_echo_comment(sprintf('%s is deprecated', $old));
        pake_echo_comment(sprintf(' use %s', $new));
      }
    }

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_8_view_yml($app_dir)
{
  pake_echo_action('upgrade 0.8', 'upgrading view configuration');

  $yml_files = pakeFinder::type('file')->name('*.yml')->in($app_dir);
  foreach ($yml_files as $yml_file)
  {
    $content = file_get_contents($yml_file);

    $deprecated = array(
      '/sf/js/prototype'     => '%SF_PROTOTYPE_WEB_DIR%/js',
      '/sf/css/prototype'    => '%SF_PROTOTYPE_WEB_DIR%/css',
      '/sf/js/sf_admin'      => '%SF_ADMIN_WEB_DIR%/js',
      '/sf/css/sf_admin'     => '%SF_ADMIN_WEB_DIR%/css',
      '/sf/images/sf_admin'  => '%SF_ADMIN_WEB_DIR%/images',
    );
    $seen = array();
    foreach ($deprecated as $old => $new)
    {
      $count = 0;
      $content = str_replace($old, $new, $content, $count);
      if ($count && !isset($seen[$old]))
      {
        $seen[$old] = true;
        pake_echo_comment(sprintf('%s is deprecated', $old));
        pake_echo_comment(sprintf(' use %s', $new));
      }
    }

    file_put_contents($yml_file, $content);
  }
}

function _upgrade_0_8_cache_yml($app_dir)
{
  pake_echo_action('upgrade 0.8', 'upgrading cache configuration');

  $yml_files = pakeFinder::type('files')->name('cache.yml')->in($app_dir);

  $seen = false;
  foreach ($yml_files as $yml_file)
  {
    $content = file_get_contents($yml_file);

    $count = 0;
    $content = preg_replace_callback('/type\:(\s*)(.+)$/m', '_upgrade_0_8_cache_yml_callback', $content, -1, $count);
    if ($count && !$seen)
    {
      $seen = true;
      pake_echo_comment('"type" has been removed in cache.yml');
      pake_echo_comment(' read the doc about "with_layout"');
    }

    file_put_contents($yml_file, $content);
  }
}

function _upgrade_0_8_cache_yml_callback($match)
{
  return 'with_layout:'.str_repeat(' ', max(1, strlen($match[1]) - 6)).(0 === strpos($match[2], 'page') ? 'true' : 'false');
}

function _upgrade_0_8_deprecated_for_generator($app_dir)
{
  pake_echo_action('upgrade 0.8', 'upgrading deprecated helpers in generator.yml');

  $yml_files = pakeFinder::type('files')->name('generator.yml')->in($app_dir);

  $seen = array();
  $deprecated_str = array(
    'admin_input_upload_tag' => 'admin_input_file_tag',
  );
  foreach ($yml_files as $yml_file)
  {
    foreach ($deprecated_str as $old => $new)
    {
      $content = file_get_contents($yml_file);

      $count = 0;
      $content = str_replace($old, $new, $content, $count);
      if ($count && !isset($seen[$old]))
      {
        $seen[$old] = true;
        pake_echo_comment(sprintf('%s() has been removed', $old));
        pake_echo_comment(sprintf(' use %s()', $new));
      }
    }

    file_put_contents($yml_file, $content);
  }
}

function _upgrade_0_8_deprecated_for_actions($action_dirs)
{
  pake_echo_action('upgrade 0.8', 'upgrading deprecated methods in actions');

  $php_files = pakeFinder::type('file')->name('*.php')->in($action_dirs);
  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    $deprecated = array(
      '$this->addHttpMeta'   => '$this->getContext()->getResponse()->addHttpMeta',
      '$this->addMeta'       => '$this->getContext()->getResponse()->addMeta',
      '$this->setTitle'      => '$this->getContext()->getResponse()->setTitle',
      '$this->addStylesheet' => '$this->getContext()->getResponse()->addStylesheet',
      '$this->addJavascript' => '$this->getContext()->getResponse()->addJavascript',
    );
    $seen = array();
    foreach ($deprecated as $old => $new)
    {
      $count = 0;
      $content = str_replace($old, $new, $content, $count);
      if ($count && !isset($seen[$old]))
      {
        $seen[$old] = true;
        pake_echo_comment(sprintf('%s has been removed', $old));
        pake_echo_comment(sprintf(' use %s', $new));
      }
    }

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_8_deprecated_for_templates($template_dirs)
{
  pake_echo_action('upgrade 0.8', 'upgrading deprecated helpers');

  $php_files = pakeFinder::type('file')->name('*.php')->in($template_dirs);
  $seen = array();
  $deprecated_str = array(
    'use_helpers'                   => 'use_helper',
    'object_admin_input_upload_tag' => 'object_admin_input_file_tag',
    'input_upload_tag'              => 'input_file_tag',
  );
  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    $count = 0;
    $content = preg_replace('#<\?php\s+include_javascripts\(\);?\s*\?>#', '', $content, -1, $count);
    if ($count && !isset($seen['include_javascripts']))
    {
      $seen['include_javascripts'] = true;
      pake_echo_comment('include_javascripts() has been removed');
    }

    $content = preg_replace('#<\?php\s+include_stylesheets\(\);?\s*\?>#', '', $content, -1, $count);
    if ($count && !isset($seen['include_stylesheets']))
    {
      $seen['include_stylesheets'] = true;
      pake_echo_comment('include_stylesheets() has been removed');
    }

    foreach ($deprecated_str as $old => $new)
    {
      $content = str_replace($old, $new, $content, $count);
      if ($count && !isset($seen[$old]))
      {
        $seen[$old] = true;
        pake_echo_comment(sprintf('%s() has been removed', $old));
        pake_echo_comment(sprintf(' use %s()', $new));
      }
    }

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_8_config_php($app_dir)
{
  pake_echo_action('upgrade 0.8', 'upgrading config.php');

  $config_file = $app_dir.DIRECTORY_SEPARATOR.sfConfig::get('sf_config_dir_name').DIRECTORY_SEPARATOR.'config.php';

  $config_php = file_get_contents($config_file);

  $replace_string = "sfConfig::get('sf_lib_dir').PATH_SEPARATOR.\n  sfConfig::get('sf_root_dir').PATH_SEPARATOR";
  if (!preg_match('/'.preg_quote($replace_string, '/').'/', $config_php))
  {
    $config_php = str_replace('sfConfig::get(\'sf_lib_dir\').PATH_SEPARATOR', $replace_string, $config_php);
  }

  file_put_contents($config_file, $config_php);
}

function _upgrade_0_8_propel_model()
{
  pake_echo_action('upgrade 0.8', 'upgrading require in models');

  $seen = false;
  $php_files = pakeFinder::type('file')->name('*.php')->in(sfConfig::get('sf_lib_dir').'/model');
  foreach ($php_files as $php_file)
  {
    $content = file_get_contents($php_file);

    $count1 = 0;
    $count2 = 0;
    $content = str_replace('require_once \'model', 'require_once \'lib/model', $content, $count1);
    $content = str_replace('include_once \'model', 'include_once \'lib/model', $content, $count2);
    if (($count1 || $count2) && !$seen)
    {
      $seen = true;
      pake_echo_comment('model require must be lib/model/...');
      pake_echo_comment('  instead of model/...');
    }

    file_put_contents($php_file, $content);
  }
}

function _upgrade_0_8_schemas()
{
  pake_echo_action('upgrade 0.8', 'upgrading schemas');

  $seen = false;
  $xml_files = pakeFinder::type('file')->name('*schema.xml')->in(sfConfig::get('sf_config_dir'));
  foreach ($xml_files as $xml_file)
  {
    $content = file_get_contents($xml_file);

    if (preg_match('/<database[^>]*package[^>]*>/', $content))
    {
      continue;
    }

    $count = 0;
    $content = str_replace('<database', '<database package="lib.model"', $content, $count);
    if ($count && !$seen)
    {
      $seen = true;
      pake_echo_comment('schema.xml must now have a database package');
      pake_echo_comment('  default is package="lib.model"');
    }

    file_put_contents($xml_file, $content);
  }
}

function _upgrade_0_8_propel_ini()
{
  pake_echo_action('upgrade 0.8', 'upgrading propel.ini configuration file');

  $propel_file = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini';

  if (is_readable($propel_file))
  {
    $propel_ini = file_get_contents($propel_file);

    // new target package (needed for new plugin system)
    $propel_ini = preg_replace('#propel\.targetPackage(\s*)=(\s*)model#', 'propel.targetPackage$1=$2lib.model', $propel_ini);
    $propel_ini = preg_replace('#propel.php.dir(\s*)=(\s*)\${propel.output.dir}/lib#', 'propel.php.dir$1=$2\${propel.output.dir}', $propel_ini);

    if (false === strpos($propel_ini, 'propel.packageObjectModel'))
    {
      $propel_ini = rtrim($propel_ini);
      $propel_ini .= "\npropel.packageObjectModel = true\n";
    }

    // new propel builder class to be able to remove require_* and strip comments
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5ExtensionObjectBuilder', 'symfony.addon.propel.builder.SfExtensionObjectBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5ExtensionPeerBuilder', 'symfony.addon.propel.builder.SfExtensionPeerBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5MultiExtendObjectBuilder', 'symfony.addon.propel.builder.SfMultiExtendObjectBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5MapBuilderBuilder', 'symfony.addon.propel.builder.SfMapBuilderBuilder', $propel_ini);

    if (false === strpos($propel_ini, 'addIncludes'))
    {
      $propel_ini .= <<<EOF

propel.builder.addIncludes = false
propel.builder.addComments = false

EOF;

      pake_echo_comment('there are 2 new propel.ini options:');
      pake_echo_comment(' - propel.builder.addIncludes');
      pake_echo_comment(' - propel.builder.addComments');

    }

    file_put_contents($propel_file, $propel_ini);
  }
}
