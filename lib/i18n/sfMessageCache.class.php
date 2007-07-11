<?php

/**
 * Translation table cache.
 *
 * @author     Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version    $Id$
 * @package    symfony
 * @subpackage i18n
 */

/**
 * Cache the translation table into the file system.
 *
 * It can cache each catalogue + variant or just the whole section.
 *
 * @package System.I18N.core
 * @author $Author: weizhuo $
 * @version $Id$
 */
class sfMessageCache
{
  protected $cache;

  /**
   * Constructor.
   *
   * @param sfCache An sfCache instance
   */
  public function __construct(sfCache $cache)
  {
    $this->cache = $cache;
  }

  /**
   * Gets the data from the cache.
   *
   * @param string $catalogue The translation section.
   * @param string $culture The translation locale, e.g. "en_AU".
   * @param string $filename If the source is a file, this file's modified time is newer than the cache's modified time, no cache hit.
   *
   * @return mixed Boolean false if no cache hit. Otherwise, translation table data for the specified section and locale.
   */
  public function get($catalogue, $culture, $lastmodified = 0)
  {
    if ($lastmodified <= 0 || $lastmodified > $this->cache->getLastModified($catalogue.':'.$culture))
    {
      return false;
    }

    return unserialize($this->cache->get($catalogue.':'.$culture));
  }

  /**
   * Saves the data to cache for the specified section and locale.
   *
   * @param string $catalogue The translation section.
   * @param string $culture The translation locale, e.g. "en_AU".
   * @param array $data The data to save.
   */
  public function set($catalogue, $culture, $data)
  {
    return $this->cache->set($catalogue.':'.$culture, serialize($data));
  }

  /**
   * Cleans up the cache for the specified section and locale.
   *
   * @param string $catalogue The translation section.
   * @param string $culture The translation locale, e.g. "en_AU".
   */
  public function clean($catalogue, $culture)
  {
    $this->cache->removePattern($catalogue.':*');
  }

  /**
   * Flushes the cache. Deletes all the cache files.
   */
  public function clear()
  {
    $this->cache->clean();
  }
}
