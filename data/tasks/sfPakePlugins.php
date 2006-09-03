<?php

pake_desc('install a new plugin');
pake_task('plugin-install', 'project_exists');

pake_desc('upgrade a plugin');
pake_task('plugin-upgrade', 'project_exists');

pake_desc('uninstall a plugin');
pake_task('plugin-uninstall', 'project_exists');

pake_desc('upgrade all plugins');
pake_task('plugin-upgrade-all', 'project_exists');

// symfony plugin-install pluginName
function run_plugin_install($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init();

  // install plugin
  $packages = array($args[0]);
  pake_echo_action('plugin', 'installing plugin "'.$args[0].'"');
  list($ret, $error) = _pear_run_command($config, 'install', array(), $packages);

  if ($error)
  {
    throw new Exception($error);
  }
}

function run_plugin_upgrade($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init();

  // upgrade plugin
  $packages = array($args[0]);
  pake_echo_action('plugin', 'upgrading plugin "'.$args[0].'"');
  list($ret, $error) = _pear_run_command($config, 'upgrade', array('loose' => true, 'nodeps' => true), $packages);

  if ($error)
  {
    throw new Exception($error);
  }
}

function run_plugin_uninstall($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the plugin name.');
  }

  $config = _pear_init();

  // uninstall plugin
  $packages = array($args[0]);
  pake_echo_action('plugin', 'uninstalling plugin "'.$args[0].'"');
  list($ret, $error) = _pear_run_command($config, 'uninstall', array(), $packages);

  if ($error)
  {
    throw new Exception($error);
  }
}

function run_plugin_upgrade_all($task, $args)
{
  $config = _pear_init();

  // upgrade all plugins
  pake_echo_action('plugin', 'upgrading all plugins');
  _pear_run_upgrade_all($config, sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'plugins');
}

function _pear_run_command($config, $command, $opts, $params)
{
  ob_start('_pear_echo_message', 2);
  $cmd = PEAR_Command::factory($command, $config);
  $ret = ob_get_clean();
  if (PEAR::isError($cmd))
  {
    throw new Exception($cmd->getMessage());
  }

  ob_start('_pear_echo_message', 2);
  $ok   = $cmd->run($command, $opts, $params);
  $ret .= ob_get_clean();

  $ret = trim($ret);

  return PEAR::isError($ok) ? array($ret, $ok->getMessage()) : array($ret, null);
}

function _pear_echo_message($message)
{
  $t = '';
  foreach (explode("\n", $message) as $line)
  {
    if ($line = trim($line))
    {
      $t .= pake_format_action('pear', $line);
    }
  }

  return $t;
}

function _pear_run_upgrade_all($config, $install_dir)
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

function _pear_init()
{
  require_once 'PEAR.php';
  require_once 'PEAR/Frontend.php';
  require_once 'PEAR/Config.php';
  require_once 'PEAR/Registry.php';
  require_once 'PEAR/Command.php';
  require_once 'PEAR/Remote.php';

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

  // read user/system configuration (don't use the singleton)
  $config = new PEAR_Config();
  $config_file = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'.pearrc';

  // change the configuration for symfony use
  $config->set('php_dir',  sfConfig::get('sf_root_dir').'/plugins');
  $config->set('data_dir', sfConfig::get('sf_root_dir').'/plugins');
  $config->set('test_dir', sfConfig::get('sf_root_dir').'/plugins');
  $config->set('doc_dir',  sfConfig::get('sf_root_dir').'/plugins');
  $config->set('bin_dir',  sfConfig::get('sf_root_dir').'/plugins');

  // save out configuration file
  $config->writeConfigFile($config_file, 'user');

  // use our configuration file
  $config = &PEAR_Config::singleton($config_file);

  $config->set('verbose', 1);
  $ui->setConfig($config);

  date_default_timezone_set('UTC');

  // register our channel
  $symfony_channel = array(
    'attribs' => array(
      'version' => '1.0',
      'xmlns' => 'http://pear.php.net/channel-1.0',
      'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
      'xsi:schemaLocation' => 'http://pear.php.net/dtd/channel-1.0 http://pear.php.net/dtd/channel-1.0.xsd',
    ),

    'name' => 'pear.symfony-project.com',
    'summary' => 'symfony project PEAR channel',
    'suggestedalias' => 'symfony',
    'servers' => array(
      'primary' => array(
        'rest' => array(
          'baseurl' => array(
            array(
              'attribs' => array('type' => 'REST1.0'),
              '_content' => 'http://pear.symfony-project.com/Chiara_PEAR_Server_REST/',
            ),
            array(
              'attribs' => array('type' => 'REST1.1'),
              '_content' => 'http://pear.symfony-project.com/Chiara_PEAR_Server_REST/',
            ),
          ),
        ),
      ),
    ),
    '_lastmodified' => array(
      'ETag' => "113845-297-dc93f000", 
      'Last-Modified' => date('r'),
    ),
  );
  pake_mkdirs(sfConfig::get('sf_plugins_dir').'/.channels/.alias');
  file_put_contents(sfConfig::get('sf_plugins_dir').'/.channels/pear.symfony-project.com.reg', serialize($symfony_channel));
  file_put_contents(sfConfig::get('sf_plugins_dir').'/.channels/.alias/symfony.txt', 'pear.symfony-project.com');

  // register symfony for dependencies
  $symfony = array(
    'name'          => 'symfony',
    'channel'       => 'pear.symfony-project.com',
    'date'          => date('Y-m-d'),
    'time'          => date('H:i:s'),
    'version'       => $sf_version,
    'stability'     => array('release' => 'stable', 'api' => 'stable'),
    'xsdversion'    => '2.0',
    '_lastmodified' => time(),
  );
  $dir = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'.registry'.DIRECTORY_SEPARATOR.'.channel.pear.symfony-project.com';
  pake_mkdirs($dir);
  file_put_contents($dir.DIRECTORY_SEPARATOR.'symfony.reg', serialize($symfony));

  return $config;
}
