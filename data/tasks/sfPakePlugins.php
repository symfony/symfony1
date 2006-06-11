<?php

pake_desc('install a new plugin');
pake_task('plugin-install', 'project_exists');

pake_desc('upgrade a plugin');
pake_task('plugin-upgrade', 'project_exists');

pake_desc('uninstall a plugin');
pake_task('plugin-uninstall', 'project_exists');

pake_desc('upgrade all plugins');
pake_task('plugin-upgrade-all', 'project_exists');

function _pear_get_method($args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must indicate where you want to install your plugin (local or global).');
  }

  $method = strtolower($args[0]);

  if (!in_array($method, array('local', 'global')))
  {
    throw new Exception('You must indicate where you want to install your plugin (local or global).');
  }

  return $method;
}

// symfony plugin-install [local|global] pluginName
function run_plugin_install($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $method = _pear_get_method($args);

  if (!isset($args[1]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init($method);

  // install plugin
  $packages = array($args[1]);
  if ($verbose) echo '>> plugin    '.pakeApp::excerpt('installing plugin "'.$args[1].'"')."\n";
  $ret = _pear_run_command($config, 'install', array(), $packages);
  if ($ret && !strpos($ret, 'not installed'))
  {
    throw new Exception($ret);
  }
}

function run_plugin_upgrade($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $method = _pear_get_method($args);

  if (!isset($args[1]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init($method);

  // upgrade plugin
  $packages = array($args[1]);
  if ($verbose) echo '>> plugin    '.pakeApp::excerpt('upgrading plugin "'.$args[1].'"')."\n";
  $ret = _pear_run_command($config, 'upgrade', array('offline' => true), $packages);
  if ($ret && !strpos($ret, 'not installed'))
  {
    throw new Exception($ret);
  }
}

function run_plugin_uninstall($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $method = _pear_get_method($args);

  if (!isset($args[1]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init($method);

  // install plugin
  $packages = array($args[1]);
  if ($verbose) echo '>> plugin    '.pakeApp::excerpt('uninstalling plugin "'.$args[1].'"')."\n";
  $ret = _pear_run_command($config, 'uninstall', array(), $packages);
  if ($ret)
  {
    throw new Exception($ret);
  }
}

function run_plugin_upgrade_all($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $method = 'local';

  $config = _pear_init($method);

  // upgrade all plugins
  if ($verbose) echo '>> plugin    '.pakeApp::excerpt('upgrading all plugins')."\n";
  _pear_run_upgrade($config, sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'plugins');
}

function _pear_run_command($config, $command, $opts, $params)
{
  ob_start();
  $cmd = PEAR_Command::factory($command, $config);
  $ret = ob_get_clean();
  if (PEAR::isError($cmd))
  {
    throw new Exception($cmd->getMessage());
  }

  ob_start();
  $ok   = $cmd->run($command, $opts, $params);
  $ret .= ob_get_clean();

  return (PEAR::isError($ok) ? $ret.$ok->getMessage() : null);
}

function _pear_run_upgrade($config, $install_dir)
{
  $registry = new PEAR_Registry($install_dir);
  $remote = new PEAR_Remote($config);
  $cmd = &PEAR_Command::factory('upgrade', $config);

  $pkgs = $registry->listPackages();
  foreach ($pkgs as $pkg)
  {
    $remoteInfo = $remote->call('package.info', $pkg);
    $versions = array_keys($remoteInfo['releases']);
    $last = $versions[0];
    $info = $registry->packageInfo($pkg);
    $current = $info['version'];
    if ($current < $last)
    {
      $ok = $cmd->run("upgrade", array(), $pkgs);
      if (PEAR::isError($ok))
      {
        throw new Exception($ok->getMessage());
      }
    }
  }
}

function _pear_init($method = 'local')
{
  require_once 'PEAR.php';
  require_once 'PEAR/Frontend.php';
  require_once 'PEAR/Config.php';
  require_once 'PEAR/Registry.php';
  require_once 'PEAR/Command.php';
  require_once 'PEAR/Remote.php';

  $install_lib_dir  = sfConfig::get('sf_plugin_lib_dir');
  $install_data_dir = sfConfig::get('sf_plugin_data_dir');

  if (!is_dir($install_lib_dir))
  {
    pake_mkdirs($install_lib_dir);
  }

  if (!is_dir($install_data_dir))
  {
    pake_mkdirs($install_data_dir);
  }

  // current symfony release
  if (is_readable('lib/symfony'))
  {
    $sf_version = file_get_contents('lib/symfony/BRANCH');
  }
  else
  {
    // PEAR config
    if ((include('symfony/pear.php')) != 'OK')
    {
      throw new Exception('Unable to find symfony librairies.');
    }
  }

  // PEAR
  PEAR_Command::setFrontendType('CLI');
  $ui = &PEAR_Command::getFrontendObject();
  $config = &PEAR_Config::singleton();
  if ($method == 'local')
  {
    $config->set('php_dir',  $install_lib_dir);
    $config->set('data_dir', $install_data_dir);
    $config->set('bin_dir',  sfConfig::get('sf_bin_dir'));
    $config->set('test_dir', sfConfig::get('sf_test_dir'));
    $config->set('doc_dir',  sfConfig::get('sf_doc_dir'));
  }
  $config->set('verbose', 1);
  $ui->setConfig($config);

  // for local installation
  if ($method == 'local')
  {
    // register our channel
    $ret = _pear_run_command($config, 'channel-discover', array(), array('pear.symfony-project.com'));
    if ($ret && !strpos($ret, 'already initialized'))
    {
      throw new Exception($ret);
    }

    // fake symfony registration for dependencies to work locally
    $symfony = array(
      'name'          => 'symfony',
      'channel'       => 'pear.symfony-project.com',
      'date'          => '2005-12-10',
      'time'          => '23:34:49',
      'version'       => $sf_version,
      'stability'     => array('release' => 'stable', 'api' => 'stable'),
      'xsdversion'    => '2.0',
      '_lastmodified' => time(),
    );
    file_put_contents($install_lib_dir.DIRECTORY_SEPARATOR.'.registry'.DIRECTORY_SEPARATOR.'.channel.pear.symfony-project.com'.DIRECTORY_SEPARATOR.'symfony.reg', serialize($symfony));
  }

  return $config;
}
