<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class to cache the HTML results for actions and templates.
 *
 * This class is based on the PEAR_Cache_Lite class.
 * All cache files are stored in files in the [sf_root_dir].'/cache/'.[sf_app].'/template' directory.
 * To disable all caching, you can set to false [sf_cache] setting.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Fabien Marty <fab@php.net>
 * @version    SVN: $Id$
 */
class sfFileCache extends sfCache
{
  const DEFAULT_NAMESPACE = '';

  /**
  * Directory where to put the cache files
  * (make sure to add a trailing slash)
  *
  * @var string
  */
  protected $cacheDir = '';

  /**
  * Enable / disable fileLocking
  *
  * (can avoid cache corruption under bad circumstances)
  *
  * @var boolean $fileLocking
  */
  protected $fileLocking = true;

  /**
  * Enable / disable write control (the cache is read just after writing to detect corrupt entries)
  *
  * Enable write control will lightly slow the cache writing but not the cache reading
  * Write control can detect some corrupt cache files but maybe it's not a perfect control
  *
  * @var boolean $writeControl
  */
  protected $writeControl = true;

  /**
  * Enable / disable read control
  *
  * If enabled, a control key is embeded in cache file and this key is compared with the one
  * calculated after the reading.
  *
  * @var boolean $readControl
  */
  protected $readControl = false;

  /**
  * File Name protection
  *
  * if set to true, you can use any cache id or namespace name
  * if set to false, it can be faster but cache ids and namespace names
  * will be used directly in cache file names so be carefull with
  * special characters...
  *
  * @var boolean $fileNameProtection
  */
  protected $fileNameProtection = false;

  /**
  * Disable / Tune the automatic cleaning process
  *
  * The automatic cleaning process destroy too old (for the given life time)
  * cache files when a new cache file is written.
  * 0               => no automatic cache cleaning
  * 1               => systematic cache cleaning
  * x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
  *
  * @var int $automaticCleaning
  */
  protected $automaticCleaningFactor = 500;
  
  /**
  * Nested directory level
  *
  * @var int $hashedDirectoryLevel
  */
  protected $hashedDirectoryLevel = 0;

  private
    $suffix = '.cache';

  /**
  * Constructor
  *
  * $options = array(
  *     'readControl' => enable / disable read control (boolean),
  *     'fileNameProtection' => enable / disable automatic file name protection (boolean),
  *     'automaticCleaningFactor' => disable / tune automatic cleaning process (int)
  *     'hashedDirectoryLevel' => level of the hashed directory system (int)
  * );
  */
  public function __construct($cacheDir)
  {
    $this->setCacheDir($cacheDir);
  }

  public function setSuffix($suffix)
  {
    $this->suffix = $suffix;
  }

  /**
   * enable / disable write control
   * @param boolean
   */
   public function setWriteControl($boolean)
   {
     $this->writeControl = $boolean;
   }

   public function getWriteControl()
   {
     return $this->writeControl;
   }

  /**
   * enable / disable fileLocking
   * @param boolean
   */
   public function setFileLocking($boolean)
   {
     $this->fileLocking = $boolean;
   }

   public function getFileLocking()
   {
     return $this->fileLocking;
   }

   /**
    * @param string directory where to put the cache files
    */
    public function setCacheDir($cacheDir)
    {
      // create cache dir if needed
      if (!is_dir($cacheDir))
      {
        $current_umask = umask(0000);
        @mkdir($cacheDir, 0777, true);
        umask($current_umask);
      }

      $this->cacheDir = $cacheDir;
    }

    public function getCacheDir()
    {
      return $this->cacheDir;
    }

  /**
  * Test if a cache is available and (if yes) return it
  *
  * @param  string  $id cache id
  * @param  string  $namespace name of the cache namespace
  * @param  boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
  * @return string  data of the cache (or null if no cache available)
  */
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    $data = null;

    list($path, $file) = $this->getFileName($id, $namespace);

    if ($doNotTestCacheValidity)
    {
      if (file_exists($path.$file))
      {
        $data = $this->read($path, $file);
      }
    }
    else
    {
      if ((file_exists($path.$file)) && (@filemtime($path.$file) > $this->refreshTime))
      {
        $data = $this->read($path, $file);
      }
    }

    return $data ? $data : null;
  }

  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    list($path, $file) = $this->getFileName($id, $namespace);

    $data = 0;
    if ($doNotTestCacheValidity)
    {
      if (file_exists($path.$file)) return 1;
    }
    else
    {
      if ((file_exists($path.$file)) && (@filemtime($path.$file) > $this->refreshTime)) return 1;
    }

    return 0;
  }
  
  /**
  * Save some data in a cache file
  *
  * @param string $data data to put in cache
  * @param string $id cache id
  * @param string $namespace name of the cache namespace
  * @return boolean true if no problem
  */
  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data)
  {
    list($path, $file) = $this->getFileName($id, $namespace);

    if ($this->automaticCleaningFactor > 0)
    {
      $rand = rand(1, $this->automaticCleaningFactor);
      if ($rand == 1)
      {
        $this->clean(false, 'old');
      }
    }

    if ($this->writeControl)
    {
      if (!$this->writeAndControl($path, $file, $data))
      {
        @touch($path.$file, time() - 2 * abs($this->lifeTime));
        return false;
      }
      else
      {
        return true;
      }
    }
    else
    {
      $ret = $this->write($path, $file, $data);

      return $ret;
    }
  }

  /**
  * Remove a cache file
  *
  * @param string $id cache id
  * @param string $namespace name of the cache namespace
  * @return boolean true if no problem
  */
  public function remove($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    list($path, $file) = $this->getFileName($id, $namespace);

    return $this->unlink($path.$file);
  }

  /**
  * Clean the cache
  *
  * if no namespace is specified all cache files will be destroyed
  * else only cache files of the specified namespace will be destroyed
  *
  * @param string $namespace name of the cache namespace
  * @return boolean true if no problem
  */
  public function clean($namespace = null, $mode = 'all')
  {
    $namespace = str_replace('/', DIRECTORY_SEPARATOR, $namespace);

    return $this->cleanDir($this->cacheDir.DIRECTORY_SEPARATOR.$namespace, $mode);
  }

  public function lastModified($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    list($path, $file) = $this->getFileName($id, $namespace);

    return (file_exists($path.$file) ? filemtime($path.$file) : 0);
  }

  /**
  * Make a file name (with path)
  *
  * @param string $id cache id
  * @param string $namespace name of the namespace
  */
  private function getFileName($id, $namespace)
  {
    $file = ($this->fileNameProtection) ? md5($id).$this->suffix : $id.$this->suffix;

    if ($namespace)
    {
      $namespace = str_replace('/', DIRECTORY_SEPARATOR, $namespace);
      $path = $this->cacheDir.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR;
    }
    else
    {
      $path = $this->cacheDir.DIRECTORY_SEPARATOR;
    }
    if ($this->hashedDirectoryLevel > 0)
    {
      $hash = md5($file);
      for ($i = 0; $i < $this->hashedDirectoryLevel; $i++)
      {
        $path = $path.substr($hash, 0, $i + 1).DIRECTORY_SEPARATOR;
      }
    }

    return array($path, $file);
  }

  /**
  * Remove a file
  * 
  * @param string $file complete file path and name
  * @return boolean true if no problem
  */
  private function unlink($file)
  {
    return @unlink($file) ? 1 : 0;
  }

  /**
  * Recursive function for cleaning cache file in the given directory
  *
  * @param  string  $dir directory complete path
  * @param  string  $namespace name of the cache namespace
  * @param  string  $mode flush cache mode : 'old', 'all'
  * @return boolean true if no problem
  */
  private function cleanDir($dir, $mode)
  {
    if (!($dh = opendir($dir)))
    {
      throw new sfCacheException('Unable to open cache directory "'.$dir.'"');
    }

    $result = true;
    while ($file = readdir($dh))
    {
      if (($file != '.') && ($file != '..'))
      {
        $file2 = $dir.DIRECTORY_SEPARATOR.$file;
        if (is_file($file2))
        {
          $unlink = 1;
          if ($mode == 'old')
          {
            // files older than lifeTime get deleted from cache
            if ((time() - filemtime($file2)) < $this->lifeTime)
            {
              $unlink = 0;
            }
          }

          if ($unlink)
          {
            $result = ($result and ($this->unlink($file2)));
          }
        }
        else if (is_dir($file2))
        {
          $result = ($result and ($this->cleanDir($file2.DIRECTORY_SEPARATOR, $mode)));
        }
      }
    }

    return $result;
  }

  /**
  * Read the cache file and return the content
  *
  * @return string content of the cache file
  */
  private function read($path, $file)
  {
    $fp = @fopen($path.$file, "rb");
    if ($this->fileLocking)
    {
      @flock($fp, LOCK_SH);
    }
    if ($fp)
    {
      clearstatcache(); // because the filesize can be cached by PHP itself...
      $length = @filesize($path.$file);
      $mqr = get_magic_quotes_runtime();
      set_magic_quotes_runtime(0);
      if ($this->readControl)
      {
        $hashControl = @fread($fp, 32);
        $length = $length - 32;
      } 
      $data = ($length) ? @fread($fp, $length) : '';
      set_magic_quotes_runtime($mqr);
      if ($this->fileLocking)
      {
        @flock($fp, LOCK_UN);
      }
      @fclose($fp);
      if ($this->readControl)
      {
        $hashData = $this->hash($data);
        if ($hashData != $hashControl)
        {
          @touch($path.$file, time() - 2 * abs($this->lifeTime));
          return false;
        }
      }

      return $data;
    }

    throw new sfCacheException('Unable to read cache file "'.$path.$file.'"');
  }

  /**
  * Write the given data in the cache file
  *
  * @param  string  $data data to put in cache
  * @return boolean true if ok
  */
  private function write($path, $file, $data)
  {
    $try = 1;
    while ($try <= 2)
    {
      $fp = @fopen($path.$file, 'wb');
      if ($fp)
      {
        if ($this->fileLocking)
        {
          @flock($fp, LOCK_EX);
        }
        if ($this->readControl)
        {
          @fwrite($fp, $this->hash($data), 32);
        }
        $len = strlen($data);
        @fwrite($fp, $data, $len);
        if ($this->fileLocking)
        {
          @flock($fp, LOCK_UN);
        }
        @fclose($fp);

        // change file mode
        $current_umask = umask();
        umask(0000);
        chmod($path.$file, 0666);
        umask($current_umask);

        return true;
      }
      else
      {
        if ($try == 1 && !is_dir($path))
        {
          // create directory structure if needed
          $current_umask = umask(0000);
          @mkdir($path, 0777, true);
          umask($current_umask);

          $try = 2;
        }
        else
        {
          $try = 999;
        }
      }
    }

    throw new sfCacheException('Unable to write cache file "'.$path.$file.'"');
  }
  
  /**
  * Write the given data in the cache file and control it just after to avoir corrupted cache entries
  *
  * @param string $data data to put in cache
  * @return boolean true if the test is ok
  */
  private function writeAndControl($path, $file, $data)
  {
    $this->write($path, $file, $data);
    $dataRead = $this->read($path, $file);

    return ($dataRead == $data);
  }
  
  /**
  * Make a control key with the string containing datas
  *
  * @param string $data data
  * @return string control key
  */
  private function hash($data)
  {
    return sprintf('% 32d', crc32($data));
  }
}

?>