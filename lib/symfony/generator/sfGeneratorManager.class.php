<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGeneratorManager
{
  private $cache = null;

  public function initialize ()
  {
    // create cache instance
    $this->cache = new sfFileCache(SF_MODULE_CACHE_DIR);
    $this->cache->setSuffix('');
  }

  public function getCache()
  {
    return $this->cache;
  }

  public function generate ($class, $param)
  {
    $generator = new $class();
    $generator->initialize($this);
    $data = $generator->generate($class, $param);

    return $data;
  }
}

?>