<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/pake/pakeFinder.class.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));
sfConfig::set('sf_symfony_data_dir', realpath($_test_dir.'/../data'));

class testAutoloader
{
  static public $class_paths = array();

  static public function initialize($with_cache = true)
  {
    require_once('System.php');
    $tmp_dir = System::tmpdir();
    if (is_readable($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php'))
    {
      self::$class_paths = unserialize(file_get_contents($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php'));
    }
    else
    {
      $files = pakeFinder::type('file')->name('*.class.php')->ignore_version_control()->in(realpath(dirname(__FILE__).'/../../lib'));
      self::$class_paths = array();
      foreach ($files as $file)
      {
        preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
        foreach ($classes[1] as $class)
        {
          self::$class_paths[$class] = $file;
        }
      }

      if ($with_cache)
      {
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php', serialize(self::$class_paths));
      }
    }
  }

  static public function __autoload($class)
  {
    if (isset(self::$class_paths[$class]))
    {
      require(self::$class_paths[$class]);

      return true;
    }

    return false;
  }

  static public function removeCache()
  {
    unlink(System::tmpdir().DIRECTORY_SEPARATOR.'sf_autoload_paths.php');
  }
}

testAutoloader::initialize();

function __autoload($class)
{
  return testAutoloader::__autoload($class);
}

class sfException extends Exception
{
  private $name = null;

  protected function setName ($name)
  {
    $this->name = $name;
  }

  public function getName ()
  {
    return $this->name;
  }
}
