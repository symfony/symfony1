<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores content in files.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFileCache extends sfCache
{
  const READ_DATA = 1;
  const READ_TIMEOUT = 2;
  const READ_LAST_MODIFIED = 4;

  const EXTENSION = '.cache';

 /**
  * Initializes this sfCache instance.
  *
  * Available parameters:
  *
  * * cacheDir: The directory where to put cache files
  *
  * * see sfCache for default parameters available for all drivers
  *
  * @param  array An associative array of initialization parameters.
  *
  * @return bool  true, if initialization completes successfully, otherwise false.
  *
  * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfCache.
  */
  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    if (!$this->getParameter('cacheDir'))
    {
      throw new sfInitializationException('You must pass a "cacheDir" parameter to initialize a sfFileCache object.');
    }

    $this->setCacheDir($this->getParameter('cacheDir'));
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    if (!$this->has($key))
    {
      return $default;
    }

    return $this->read($this->getFilePath($key));
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return file_exists($this->getFilePath($key)) && time() < $this->read($this->getFilePath($key), self::READ_TIMEOUT);
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    if ($this->getParameter('automaticCleaningFactor') > 0 && rand(1, $this->getParameter('automaticCleaningFactor')) == 1)
    {
      $this->clean(sfCache::OLD);
    }

    $lifetime = is_null($lifetime) ? $this->getParameter('lifetime') : $lifetime;

    return $this->write($this->getFilePath($key), $data, time() + $lifetime);
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return @unlink($this->getFilePath($key));
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    if (false !== strpos($pattern, '**'))
    {
      $pattern = str_replace(sfCache::SEPARATOR, DIRECTORY_SEPARATOR, $pattern).self::EXTENSION;

      $regexp = self::patternToRegexp($pattern);
      $paths = array();
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getParameter('cacheDir'))) as $path)
      {
        if (preg_match($regexp, str_replace($this->getParameter('cacheDir').DIRECTORY_SEPARATOR, '', $path)))
        {
          $paths[] = $path;
        }
      }
    }
    else
    {
      $paths = glob($this->getParameter('cacheDir').DIRECTORY_SEPARATOR.str_replace(sfCache::SEPARATOR, DIRECTORY_SEPARATOR, $pattern).self::EXTENSION);
    }

    foreach ($paths as $path)
    {
      if (is_dir($path))
      {
        sfToolkit::clearDirectory($path);
      }
      else
      {
        @unlink($path);
      }
    }
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (!is_dir($this->getParameter('cacheDir')))
    {
      throw new sfCacheException(sprintf('Unable to open cache directory "%s"', $this->getParameter('cacheDir')));
    }

    $result = true;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getParameter('cacheDir'))) as $file)
    {
      if (sfCache::ALL == $mode || time() > $this->read($file, self::READ_TIMEOUT))
      {
        $result = $result && @unlink($file);
      }
    }

    return $result;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    $path = $this->getFilePath($key);

    if (!file_exists($path))
    {
      return 0;
    }

    $timeout = $this->read($path, self::READ_TIMEOUT);

    return $timeout < time() ? 0 : $timeout;
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    $path = $this->getFilePath($key);

    if (!file_exists($path) || $this->read($path, self::READ_TIMEOUT) < time())
    {
      return 0;
    }

    return $this->read($path, self::READ_LAST_MODIFIED);
  }

 /**
  * Converts a cache key to a full path.
  *
  * @param string  The cache key
  *
  * @return string The full path to the cache file
  */
  protected function getFilePath($key)
  {
    return $this->getParameter('cacheDir').DIRECTORY_SEPARATOR.str_replace(sfCache::SEPARATOR, DIRECTORY_SEPARATOR, $key).self::EXTENSION;
  }

 /**
  * Reads the cache file and returns the content.
  *
  * @param string The file path
  * @param mixed  The type of data you want to be returned
  *               sfFileCache::READ_DATA: The cache content
  *               sfFileCache::READ_TIMEOUT: The timeout
  *
  * @return string The content of the cache file.
  *
  * @throws sfCacheException
  */
  protected function read($path, $type = self::READ_DATA)
  {
    if (!$fp = @fopen($path, 'rb'))
    {
      throw new sfCacheException(sprintf('Unable to read cache file "%s"', $path));
    }

    @flock($fp, LOCK_SH);
    clearstatcache(); // because the filesize can be cached by PHP itself...
    $length = @filesize($path);
    $mqr = get_magic_quotes_runtime();
    set_magic_quotes_runtime(0);
    switch ($type)
    {
      case self::READ_TIMEOUT:
        $data = $length ? intval(@fread($fp, 12)) : 0;
        break;
      case self::READ_LAST_MODIFIED:
        @fseek($fp, 12);
        $data = $length ? intval(@fread($fp, 12)) : 0;
        break;
      case self::READ_DATA:
        if ($length)
        {
          @fseek($fp, 24);
          $data = @fread($fp, $length - 24);
        }
        else
        {
          $data = '';
        }
        break;
      default:
        throw new sfConfigurationException(sprintf('Unknown type "%s"', $type));
    }
    set_magic_quotes_runtime($mqr);
    @flock($fp, LOCK_UN);
    @fclose($fp);

    return $data;
  }

 /**
  * Writes the given data in the cache file.
  *
  * @param  string  The file path
  * @param  string  The data to put in cache
  * @param  integer The timeout timestamp
  *
  * @return boolean true if ok, otherwise false
  *
  * @throws sfCacheException
  */
  protected function write($path, $data, $timeout)
  {
    $current_umask = umask();
    umask(0000);

    if (!is_dir(dirname($path)))
    {
      // create directory structure if needed
      mkdir(dirname($path), 0777, true);
    }

    if (!$fp = @fopen($path, 'wb'))
    {
      throw new sfCacheException(sprintf('Unable to write cache file "%s"', $path));
    }

    @flock($fp, LOCK_EX);
    @fwrite($fp, str_pad($timeout, 12, 0, STR_PAD_LEFT));
    @fwrite($fp, str_pad(time(), 12, 0, STR_PAD_LEFT));
    @fwrite($fp, $data);
    @flock($fp, LOCK_UN);
    @fclose($fp);

    // change file mode
    chmod($path, 0666);

    umask($current_umask);

    return true;
  }

  /**
   * Sets the cache root directory.
   *
   * @param string The directory where to put the cache files
   */
  protected function setCacheDir($cacheDir)
  {
    // remove last DIRECTORY_SEPARATOR
    if (DIRECTORY_SEPARATOR == substr($cacheDir, -1))
    {
      $cacheDir = substr($cacheDir, 0, -1);
    }

    // create cache dir if needed
    if (!is_dir($cacheDir))
    {
      $current_umask = umask(0000);
      @mkdir($cacheDir, 0777, true);
      umask($current_umask);
    }
  }
}
