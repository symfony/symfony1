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
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfSimpleAutoload
{
  protected
    $cacheFile    = null,
    $cacheLoaded  = false,
    $cacheChanged = false,
    $dirs         = array(),
    $files        = array(),
    $classes      = array();

  public function __construct($id = '', $withCache = true)
  {
    if ($withCache)
    {
      require_once(dirname(__FILE__).'/sfToolkit.class.php');

      $this->cacheFile = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_simple_autoload_cache_%s_%s.php', $id, md5(__FILE__));
    }

    $this->loadCache();
  }

  public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    spl_autoload_register(array($this, 'autoload'));

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

    $this->cacheLoaded = true;

    list($this->classes, $this->dirs, $this->files) = unserialize(file_get_contents($this->cacheFile));
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
  }

  public function removeCache()
  {
    @unlink($this->cacheFile);
  }

  public function addDirectory($dir, $ext = '.php')
  {
    if (!is_dir($dir))
    {
      return;
    }

    require_once(dirname(__FILE__).'/sfFinder.class.php');

    $finder = sfFinder::type('file')->ignore_version_control()->name('*'.$ext);
    foreach (glob($dir) as $dir)
    {
      if (in_array($dir, $this->dirs) && $this->cacheLoaded)
      {
        continue;
      }

      $this->dirs[] = $dir;
      $this->cacheChanged = true;
      $files = $finder->in($dir);
      if (is_array($files))
      {
        foreach ($files as $file)
        {
          $this->addFile($file, false);
        }
      }
    }
  }

  public function addFile($file, $register = true)
  {
    if (!is_file($file))
    {
      return;
    }

    if (in_array($file, $this->files) && $this->cacheLoaded)
    {
      return;
    }

    if ($register)
    {
      $this->files[] = $file;
      $this->cacheChanged = true;
    }

    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
    foreach ($classes[1] as $class)
    {
      $this->classes[$class] = $file;
    }
  }
}
