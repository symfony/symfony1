<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfSmallObjectCache.class.php 370 2005-08-18 09:00:01Z fabien $
 */

/**
 *
 * sfSmallObjectCache class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfSmallObjectCache.class.php 370 2005-08-18 09:00:01Z fabien $
 */
abstract class sfSmallObjectCache
{
  protected static $cache = array();
  protected static $cacheLoaded = array();
  protected $type = null;

  public function __construct()
  {
    $this->type = get_class($this);
    if (!array_key_exists($this->type, sfSmallObjectCache::$cacheLoaded))
    {
      sfSmallObjectCache::$cache[$this->type] = array();
      sfSmallObjectCache::$cacheLoaded[$this->type] = false;
    }
  }

  public function clear()
  {
    if (file_exists($this->getCacheFile())) unlink($this->getCacheFile());
  }

  public function getAll($culture)
  {
    if (!sfSmallObjectCache::$cacheLoaded[$this->type]) $this->loadCache();

    if (array_key_exists($culture, sfSmallObjectCache::$cache[$this->type]))
      return sfSmallObjectCache::$cache[$this->type][$culture];
    else
      return array();
  }

  public function getId($id, $culture)
  {
    if (!sfSmallObjectCache::$cacheLoaded[$this->type]) $this->loadCache();

    if (array_key_exists($culture, sfSmallObjectCache::$cache[$this->type]) && array_key_exists($id, sfSmallObjectCache::$cache[$this->type][$culture]))
      return sfSmallObjectCache::$cache[$this->type][$culture][$id];
    else
      return array();
  }

  protected function loadCache()
  {
    if (!file_exists($this->getCacheFile())) $this->refresh();

    sfSmallObjectCache::$cache[$this->type] = unserialize(file_get_contents($this->getCacheFile()));

    sfSmallObjectCache::$cacheLoaded[$this->type] = true;
  }

  public function refresh()
  {
    $cache = array();
    $cache = $this->doRefresh();
    file_put_contents($this->getCacheFile(), serialize($cache));
  }

  abstract public function doRefresh();

  abstract public function getCacheFile();
}

?>