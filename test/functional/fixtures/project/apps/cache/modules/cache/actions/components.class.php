<?php

/**
 * cache components.
 *
 * @package    project
 * @subpackage cache
 * @author     Your name here
 * @version    SVN: $Id$
 */
class cacheComponents extends sfComponents
{
  public function executeComponent()
  {
  }

  public function executeCacheableComponent()
  {
    $this->bar = $this->getRequestParameter('bar');
  }
}
