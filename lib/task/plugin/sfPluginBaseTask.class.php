<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all symfony plugin tasks.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfPluginBaseTask extends sfBaseTask
{
  protected function pearRunCommand($config, $command, $opts, $params)
  {
    ob_start(array($this, 'pearEchoMessage'), 2);
    $cmd = PEAR_Command::factory($command, $config);
    $ret = ob_get_clean();
    if (PEAR::isError($cmd))
    {
      throw new Exception($cmd->getMessage());
    }

    ob_start(array($this, 'pearEchoMessage'), 2);
    $ok   = $cmd->run($command, $opts, $params);
    $ret .= ob_get_clean();

    $ret = trim($ret);

    return PEAR::isError($ok) ? array($ret, $ok->getMessage()) : array($ret, null);
  }

  public function pearEchoMessage($message)
  {
    $t = '';
    foreach (explode("\n", $message) as $longline)
    {
      foreach (explode("\n", wordwrap($longline, 62)) as $line)
      {
        if ($line = trim($line))
        {
          $t .= $this->formatSection('pear', $line);
        }
      }
    }

    return $t;
  }

  protected function pearInit()
  {
    // Remove E_STRICT from error_reporting
    error_reporting(error_reporting() &~ E_STRICT);

    require_once 'PEAR.php';
    require_once 'PEAR/Frontend.php';
    require_once 'PEAR/Config.php';
    require_once 'PEAR/Registry.php';
    require_once 'PEAR/Command.php';
    require_once 'PEAR/Remote.php';

    // current symfony release
    $sf_version = preg_replace('/\-\w+$/', '', sfCore::VERSION);

    // PEAR
    PEAR_Command::setFrontendType('CLI');
    $ui = &PEAR_Command::getFrontendObject();

    // read user/system configuration (don't use the singleton)
    $config = new PEAR_Config();
    $configFile = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'.pearrc';

    // change the configuration for symfony use
    $config->set('php_dir',  sfConfig::get('sf_plugins_dir'));
    $config->set('data_dir', sfConfig::get('sf_plugins_dir'));
    $config->set('test_dir', sfConfig::get('sf_plugins_dir'));
    $config->set('doc_dir',  sfConfig::get('sf_plugins_dir'));
    $config->set('bin_dir',  sfConfig::get('sf_plugins_dir'));

    // change the PEAR temp dir
    $config->set('cache_dir',    sfConfig::get('sf_cache_dir'));
    $config->set('download_dir', sfConfig::get('sf_cache_dir'));
    $config->set('tmp_dir',      sfConfig::get('sf_cache_dir'));

    // save out configuration file
    $config->writeConfigFile($configFile, 'user');

    // use our configuration file
    $config = &PEAR_Config::singleton($configFile);

    $config->set('verbose', 1);
    $ui->setConfig($config);

    date_default_timezone_set('UTC');

    // register our channel
    $channel = array(
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
    $this->filesystem->mkdirs(sfConfig::get('sf_plugins_dir').'/.channels/.alias');
    file_put_contents(sfConfig::get('sf_plugins_dir').'/.channels/pear.symfony-project.com.reg', serialize($channel));
    file_put_contents(sfConfig::get('sf_plugins_dir').'/.channels/.alias/symfony.txt', 'pear.symfony-project.com');

    // register symfony for dependencies
    $symfony = array(
      'name'          => 'symfony',
      'channel'       => 'pear.symfony-project.com',
      'date'          => date('Y-m-d'),
      'time'          => date('H:i:s'),
      'version'       => array('release' => $sf_version, 'api' => '1.0.0'),
      'stability'     => array('release' => 'stable', 'api' => 'stable'),
      'xsdversion'    => '2.0',
      '_lastmodified' => time(),
      'old'           => array('version' => $sf_version, 'release_state' => 'stable'),
    );
    $dir = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'.registry'.DIRECTORY_SEPARATOR.'.channel.pear.symfony-project.com';
    $this->filesystem->mkdirs($dir);
    file_put_contents($dir.DIRECTORY_SEPARATOR.'symfony.reg', serialize($symfony));

    return $config;
  }

  protected function getPluginName($arg)
  {
    $pluginName = (false !== $pos = strrpos($arg, '/')) ? substr($arg, $pos + 1) : $arg;
    $pluginName = (false !== $pos = strrpos($pluginName, '-')) ? substr($pluginName, 0, $pos) : $pluginName;

    return $pluginName;
  }

  protected function installWebContent($pluginName)
  {
    $webDir = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.'web';
    if (is_dir($webDir))
    {
      $this->log($this->formatSection('plugin', 'installing web data for plugin'));
      $this->filesystem->symlink(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.$webDir, true);
    }
  }

  protected function uninstallWebContent($pluginName)
  {
    $webDir = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.'web';
    $targetDir = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$pluginName;
    if (is_dir($webDir) && is_dir($targetDir))
    {
      $this->log($this->formatSection('plugin', 'uninstalling web data for plugin'));
      if (is_link($targetDir))
      {
        $this->filesystem->remove($targetDir);
      }
      else
      {
        $this->filesystem->remove(sfFinder::type('any')->in($targetDir));
        $this->filesystem->remove($targetDir);
      }
    }
  }
}
