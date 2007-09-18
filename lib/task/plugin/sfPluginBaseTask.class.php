<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Remove E_STRICT from error_reporting
error_reporting(error_reporting() ^ E_STRICT);
date_default_timezone_set('UTC');

require_once 'PEAR.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Registry.php';
require_once 'PEAR/Command.php';
require_once 'PEAR/Remote.php';
require_once 'PEAR/Downloader.php';
require_once 'PEAR/Frontend/CLI.php';
require_once 'PEAR/PackageFile/v2/rw.php';

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
  protected
   $config   = null,
   $registry = null,
   $frontend = null;

  /**
   * @see sfTask
   */
  protected function doRun(sfCommandManager $commandManager, $options)
  {
    // initialize some PEAR objects
    $this->initConfig();
    $this->initRegistry();
    $this->initFrontend();

    // register channels
    $this->registerChannel('pear.symfony-project.com');
    $this->config->set('default_channel', 'symfony');

    // register symfony for dependencies
    $this->registerSymfonyPackage();

    return parent::doRun($commandManager, $options);
  }

  protected function pearRunCommand($command, $opts, $params)
  {
    $cmd = PEAR_Command::factory($command, $this->config);
    if (PEAR::isError($cmd))
    {
      throw new sfException('PEAR Error: '.$cmd->getMessage());
    }

    $ok = $cmd->run($command, $opts, $params);
    if (PEAR::isError($ok))
    {
      throw new sfException('PEAR Error: '.$ok->getMessage());
    }
  }

  protected function registerChannel($channel)
  {
    $this->config->set('auto_discover', true);

    if (!$this->registry->channelExists($channel, true))
    {
      $downloader = new PEAR_Downloader($this->frontend, array(), $this->config);
      if (!$downloader->discover($channel))
      {
        throw new sfException(sprintf('Unable to register channel "%s"', $channel));
      }
    }
  }

  protected function initFrontend()
  {
    $this->frontend = PEAR_Frontend::singleton('PEAR_Frontend_symfony');
    if (PEAR::isError($this->frontend))
    {
      throw new sfException('PEAR Error: '.$this->frontend->getMessage());
    }

    $this->frontend->setTask($this);
  }

  protected function initRegistry()
  {
    $this->registry = $this->config->getRegistry();
    if (PEAR::isError($this->registry))
    {
      throw new sfException(sprintf('PEAR Error: Unable to initialize PEAR registry "%s"', $this->registry->getMessage()));
    }
  }

  protected function registerSymfonyPackage()
  {
    $symfony = new PEAR_PackageFile_v2_rw();
    $symfony->setPackage('symfony');
    $symfony->setChannel('pear.symfony-project.com');
    $symfony->setConfig($this->config);
    $symfony->setPackageType('php');
    $symfony->setAPIVersion('1.1.0');
    $symfony->setAPIStability('stable');
    $symfony->setReleaseVersion(preg_replace('/\-\w+$/', '', sfCore::VERSION));
    $symfony->setReleaseStability('stable');
    $symfony->setDate(date('Y-m-d'));
    $symfony->setDescription('symfony');
    $symfony->setSummary('symfony');
    $symfony->setLicense('MIT License');
    $symfony->clearContents();
    $symfony->resetFilelist();
    $symfony->addMaintainer('lead', 'fabpot', 'Fabien Potencier', 'fabien.potencier@symfony-project.com');
    $symfony->setNotes('-');
    $symfony->setPearinstallerDep('1.4.3');
    $symfony->setPhpDep('5.1.0');

    $this->registry->deletePackage('symfony', 'pear.symfony-project.com');
    $this->registry->addPackage2($symfony);
  }

  protected function initConfig()
  {
    $this->config = PEAR_Config::singleton();

    // change the configuration for symfony use
    $this->config->set('php_dir',  sfConfig::get('sf_plugins_dir'));
    $this->config->set('data_dir', sfConfig::get('sf_plugins_dir'));
    $this->config->set('test_dir', sfConfig::get('sf_plugins_dir'));
    $this->config->set('doc_dir',  sfConfig::get('sf_plugins_dir'));
    $this->config->set('bin_dir',  sfConfig::get('sf_plugins_dir'));

    // change the PEAR temp dir
    $cacheDir = sfConfig::get('sf_root_cache_dir').'/.pear';
    $this->filesystem->mkdirs($cacheDir, 0777);
    $this->config->set('cache_dir',    $cacheDir);
    $this->config->set('download_dir', $cacheDir);
    $this->config->set('tmp_dir',      $cacheDir);

    $this->config->set('verbose', 1);
  }

  protected function getPluginName($package)
  {
    $pluginName = (false !== $pos = strrpos($package, '/')) ? substr($package, $pos + 1) : $package;
    $pluginName = (false !== $pos = strrpos($pluginName, '-')) ? substr($pluginName, 0, $pos) : $pluginName;

    return $pluginName;
  }

  protected function installWebContent($package)
  {
    $pluginName = $this->getPluginName($package);

    $webDir = sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.'web';
    if (is_dir($webDir))
    {
      $this->log($this->formatSection('plugin', 'installing web data for plugin'));

      $this->filesystem->symlink($webDir, sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$pluginName, true);
    }
  }

  protected function uninstallWebContent($package)
  {
    $pluginName = $this->getPluginName($package);

    $targetDir = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$pluginName;
    if (is_dir($targetDir))
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

class PEAR_Frontend_symfony extends PEAR_Frontend_CLI
{
  protected
    $task = null;

  public function setTask($task)
  {
    $this->task = $task;
  }

  public function _displayLine($text)
  {
    $this->_display($text);
  }

  public function _display($text)
  {
    $this->task->log($this->splitLongLine($text));
  }

  protected function splitLongLine($text)
  {
    $t = '';
    foreach (explode("\n", $text) as $longline)
    {
      foreach (explode("\n", wordwrap($longline, 62)) as $line)
      {
        if ($line = trim($line))
        {
          $t .= $this->task->formatSection('pear', $line);
        }
      }
    }

    return $t;
  }
}
