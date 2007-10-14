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

  static public function getInstance($cacheFile = null)
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfSimpleAutoload($cacheFile);
    }

    return self::$instance;
  }

  public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (!spl_autoload_register(array($this, 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class($this)));
    }

    if ($this->cacheFile)
    {
      register_shutdown_function(array($this, 'saveCache'));
    }
  }

  public function unregister()
  {
    spl_autoload_unregister(array($this, 'autoload'));
  }

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

  public function saveCache()
  {
    if ($this->cacheChanged)
    {
      file_put_contents($this->cacheFile, serialize(array($this->classes, $this->dirs, $this->files)));

      $this->cacheChanged = false;
    }
  }

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

  public function removeCache()
  {
    @unlink($this->cacheFile);
  }

  public function addDirectory($dir, $ext = '.php')
  {
    require_once(dirname(__FILE__).'/sfFinder.class.php');

    $finder = sfFinder::type('file')->ignore_version_control()->follow_link()->name('*'.$ext);
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

  public function addFiles($files, $register = true)
  {
    foreach ($files as $file)
    {
      $this->addFile($file, $register);
    }
  }

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
}
