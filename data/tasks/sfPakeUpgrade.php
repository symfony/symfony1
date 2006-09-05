<?php

pake_desc('upgrade to a new symfony release');
pake_task('upgrade', 'project_exists');

pake_desc('downgrade to a previous symfony release');
pake_task('downgrade', 'project_exists');

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
  _upgrade_0_8_propel_ini();

  // find all applications for this project
  $apps = pakeFinder::type('directory')->name(sfConfig::get('sf_app_module_dir_name'))->mindepth(1)->maxdepth(1)->relative()->in(sfConfig::get('sf_apps_dir_name'));

  // upgrade all applications
  foreach ($apps as $app_module_dir)
  {
    $app = str_replace(DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name'), '', $app_module_dir);
    pake_echo_action('upgrade 0.8', sprintf('[upgrading application "%s"]', $app));

    $app_dir = sfConfig::get('sf_apps_dir_name').'/'.$app;

    // upgrade all modules
    $dir = $app_dir.'/'.sfConfig::get('sf_app_module_dir_name');
    if ($dir)
    {
      // template dirs
      $template_dirs   = pakeFinder::type('directory')->name('templates')->mindepth(1)->maxdepth(1)->in($dir);
      $template_dirs[] = $app_dir.'/'.sfConfig::get('sf_app_template_dir_name');

      _upgrade_0_8_deprecated_for_templates($template_dirs);

      // actions dirs
      $action_dirs = pakeFinder::type('directory')->name('actions')->mindepth(1)->maxdepth(1)->in($dir);

      _upgrade_0_8_deprecated_for_actions($action_dirs);
    }
  }

  pake_echo_action('upgrade 0.8', 'done');
  pake_echo_comment('you can now:');
  pake_echo_comment(' - rebuild model: symfony propel-build-model');
  pake_echo_comment(' - clear cache: symfony cc');
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

function _upgrade_0_8_propel_ini()
{
  $propel_file = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini';

  if (is_readable($propel_file))
  {
    pake_echo_action('upgrade 0.8', 'upgrading propel.ini configuration file');
    pake_echo_comment('there are 2 new propel.ini options:');
    pake_echo_comment(' - propel.builder.addIncludes');
    pake_echo_comment(' - propel.builder.addComments');

    $propel_ini = file_get_contents($propel_file);

    // new target package (needed for new plugin system)
    $propel_ini = preg_replace('#propel\.targetPackage(\s*)=(\s*)lib\.model#', 'propel.targetPackage$1=$2lib.model', $propel_ini);
    $propel_ini = preg_replace('#propel.php.dir(\s*)=(\s*)\${propel.output.dir}/lib#', 'propel.php.dir$1=$2\${propel.output.dir}', $propel_ini);

    // new propel builder class to be able to remove require_* and strip comments
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5ExtensionObjectBuilder', 'symfony.addon.propel.builder.SfExtensionObjectBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5ExtensionPeerBuilder', 'symfony.addon.propel.builder.SfExtensionPeerBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5MultiExtendObjectBuilder', 'symfony.addon.propel.builder.SfMultiExtendObjectBuilder', $propel_ini);
    $propel_ini = str_replace('propel.engine.builder.om.php5.PHP5MapBuilderBuilder', 'symfony.addon.propel.builder.SfMapBuilderBuilder', $propel_ini);

    $propel_ini .= <<<EOF

propel.builder.addIncludes = false
propel.builder.addComments = false

EOF;

    file_put_contents($propel_file, $propel_ini);
  }
}
