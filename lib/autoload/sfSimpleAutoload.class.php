<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSimpleAutoload class.
 *
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage autoload
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfSimpleAutoload
{
  static protected
    $instance = null;

  protected
    $cacheFile    = null,
    $cacheLoaded  = false,
    $cacheChanged = false,
    $dirs         = array(),
    $files        = array(),
    $classes      = array();

  protected function __construct($cacheFile = null)
  {
    if (!is_null($cacheFile))
    {
      $this->cacheFile = $cacheFile;
    }

    $this->loadCache();
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @param  string $cacheFile  The file path to save the cache
   *
   * @return sfSimpleAutoload   A sfSimpleAutoload implementation instance.
   */
  static public function getInstance($cacheFile = null)
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfSimpleAutoload($cacheFile);
    }

    return self::$instance;
  }

  /**
   * Register sfSimpleAutoload in spl autoloader.
   *
   * @return void
   */
  static public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (!spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }

    if (self::getInstance()->cacheFile)
    {
      register_shutdown_function(array(self::getInstance(), 'saveCache'));
    }
  }

  /**
   * Unregister sfSimpleAutoload from spl autoloader.
   *
   * @return void
   */
  static public function unregister()
  {
    spl_autoload_unregister(array(self::getInstance(), 'autoload'));
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset($this->classes[$class]))
    {
      require($this->classes[$class]);

      return true;
    }

    return false;
  }

  /**
   * Loads the cache.
   */
  public function loadCache()
  {
    if (!$this->cacheFile || !is_readable($this->cacheFile))
    {
      return;
    }

    list($this->classes, $this->dirs, $this->files) = unserialize(file_get_contents($this->cacheFile));

    $this->cacheLoaded = true;
    $this->cacheChanged = false;
  }

  /**
   * Saves the cache.
   */
  public function saveCache()
  {
    if ($this->cacheChanged)
    {
      file_put_contents($this->cacheFile, serialize(array($this->classes, $this->dirs, $this->files)));

      $this->cacheChanged = false;
    }
  }

  /**
   * Reloads cache.
   */
  public function reload()
  {
    $this->classes = array();
    $this->cacheLoaded = false;

    foreach ($this->dirs as $dir)
    {
      $this->addDirectory($dir);
    }

    foreach ($this->files as $file)
    {
      $this->addFile($file);
    }

    $this->cacheLoaded = true;
    $this->cacheChanged = true;
  }

  /**
   * Removes the cache.
   */
  public function removeCache()
  {
    @unlink($this->cacheFile);
  }

  /**
   * Adds a directory to the autoloading system.
   *
   * @param string The directory to look for classes
   * @param string The extension to look for
   */
  public function addDirectory($dir, $ext = '.php')
  {
    $finder = sfFinder::type('file')->follow_link()->name('*'.$ext);
    foreach (glob($dir) as $dir)
    {
      if (in_array($dir, $this->dirs))
      {
        if ($this->cacheLoaded)
        {
          continue;
        }
      }
      else
      {
        $this->dirs[] = $dir;
      }

      $this->cacheChanged = true;
      $this->addFiles($finder->in($dir), false);
    }
  }

  /**
   * Adds files to the autoloading system.
   *
   * @param array   An array of files
   * @param Boolean Whether to register those files as single entities (used when reloading)
   */
  public function addFiles(array $files, $register = true)
  {
    foreach ($files as $file)
    {
      $this->addFile($file, $register);
    }
  }

  /**
   * Adds a file to the autoloading system.
   *
   * @param string  A file path
   * @param Boolean Whether to register those files as single entities (used when reloading)
   */
  public function addFile($file, $register = true)
  {
    if (!is_file($file))
    {
      return;
    }

    if (in_array($file, $this->files))
    {
      if ($this->cacheLoaded)
      {
        return;
      }
    }
    else
    {
      if ($register)
      {
        $this->files[] = $file;
      }
    }

    if ($register)
    {
      $this->cacheChanged = true;
    }

    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
    foreach ($classes[1] as $class)
    {
      $this->classes[$class] = $file;
    }
  }

  public function setClassPath($class, $path)
  {
    $this->overriden[$class] = $path;

    $this->classes[$class] = $path;
  }
}
