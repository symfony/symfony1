<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * .
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

require_once 'symfony/config/sfConfig.class.php';
require_once 'symfony/util/sfToolkit.class.php';
require_once 'symfony/test/sfTestBrowser.class.php';

if (!function_exists('pake_task'))
{
  require_once('pake/pakeFunction.php');
}

class sfLiveProjectUnitTestCase extends UnitTestCase
{
  private
    $browser          = null;

  private static
    $loaded           = false,
    $currentDir       = '',
    $currentLabel     = '',
    $workDir          = null;

  public function getFixturesDir()
  {
    throw new Exception('You must set your "fixtures" directory.');
  }

  public function getWorkDir()
  {
    if (self::$workDir === null)
    {
      sfToolkit::clearDirectory('/tmp/symfonylivetest');
      $root_dir = tempnam('/tmp/symfonylivetest', 'tmp');
      unlink($root_dir);
      self::$workDir = $root_dir.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
      if (!is_dir($root_dir))
      {
        mkdir($root_dir, 0777);
      }
      mkdir(self::$workDir, 0777);
    }

    return self::$workDir;
  }

  public function getPakefilePath()
  {
    return dirname(__FILE__).'/../../../data/symfony/bin/pakefile.php';
  }

  public function getProjectName()
  {
    return 'liveapp';
  }

  public function getAppName()
  {
    return 'app';
  }

  public function getEnvironment()
  {
    return 'test';
  }

  public function getDebug()
  {
    return true;
  }

  public function getSymfonyLibDir()
  {
    return dirname(__FILE__).'/../..';
  }

  public function getSymfonyDataDir()
  {
    return dirname(__FILE__).'/../../../data';
  }

  public function SetUp()
  {
    if (!self::$loaded)
    {
      self::$currentDir = getcwd();
      self::$loaded = true;
    }

    $this->initSandbox();

    chdir($this->getWorkDir());

    // initialize our test browser
    $this->browser = new sfTestBrowser();
    $this->browser->initialize('liveapp');
  }

  public function tearDown()
  {
    $this->browser->shutdown();

    chdir(self::$currentDir);
  }

  public function initSandbox()
  {
    if (self::$currentLabel == $this->getLabel())
    {
      return;
    }

    self::$currentLabel = $this->getLabel();
    self::$workDir = null;

    chdir($this->getWorkDir());

    // create a new symfony project
    $this->runSymfony('init-project '.$this->getProjectName());

    // create a new symfony application
    $this->runSymfony('init-app '.$this->getAppName());

    // initialize our testing environment
    sfConfig::add(array(
      'sf_root_dir'         => $this->getWorkDir(),
      'sf_app'              => $this->getAppName(),
      'sf_environment'      => $this->getEnvironment(),
      'sf_debug'            => $this->getDebug(),
      'sf_symfony_lib_dir'  => $this->getSymfonyLibDir(),
      'sf_symfony_data_dir' => $this->getSymfonyDataDir(),
      'sf_test'             => true,
    ));

    // get configuration
    require_once(sfConfig::get('sf_symfony_lib_dir').'/symfony/config/sfConfig.class.php');

    // directory layout
    include(sfConfig::get('sf_symfony_data_dir').'/symfony/config/constants.php');

    // include path
    set_include_path(
      sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_symfony_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_model_dir').PATH_SEPARATOR.
      get_include_path()
    );

    require_once 'symfony/symfony.php';

    register_shutdown_function(array($this, 'shutdown'));

    chdir(self::$currentDir);
  }

  public function getBrowser()
  {
    return $this->browser;
  }

  public function runSymfony($command, $options = array())
  {
    if (isset($options['quiet']) && $options['quiet'])
    {
      $command = '-q '.$command;
    }

    ob_start();

    pakeApp::get_instance()->run($this->getPakefilePath(), $command);

    return ob_get_clean();
  }

  public function initModule($name)
  {
    $this->runSymfony('init-module app '.$name, array('quiet' => true));

    $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
    pake_mirror($finder, $this->getFixturesDir().'/modules/'.$name, sfConfig::get('sf_root_dir').'/apps/app/modules/'.$name, array('override' => true));
  }

  public function checkModuleResponse($url, $check_true = array(), $check_false = array())
  {
    $html = $this->getBrowser()->get($url);

    if (!is_array($check_true))
    {
      $check_true = array($check_true);
    }
    foreach ($check_true as $check)
    {
      $this->assertWantedPattern($check, $html);
    }

    if (!is_array($check_false))
    {
      $check_false = array($check_false);
    }
    foreach ($check_false as $check)
    {
      $this->assertNoUnWantedPattern($check, $html);
    }

    return $html;
  }

  public function shutdown()
  {
    // remove all temporary files and directories
    sfToolkit::clearDirectory($this->getWorkDir());
    rmdir($this->getWorkDir());
  }
}

?>