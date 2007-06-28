<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../lib/vendor/pake/pakeFinder.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');

class testAutoloader
{
  static public $class_paths = array();

  static public function initialize($with_cache = true)
  {
    $autoload_file = self::getAutoloadFile();
    if (is_readable($autoload_file))
    {
      self::$class_paths = unserialize(file_get_contents($autoload_file));
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
        file_put_contents($autoload_file, serialize(self::$class_paths));
      }
    }
  }

  static public function autoload($class)
  {
    if (isset(self::$class_paths[$class]))
    {
      require(self::$class_paths[$class]);

      return true;
    }

    return false;
  }

  static public function getAutoloadFile()
  {
    return sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_paths_%s.php', trim(file_get_contents(dirname(__FILE__).'/../../lib/VERSION')));
  }

  static public function removeCache()
  {
    unlink(self::getAutoloadFile());
  }
}
