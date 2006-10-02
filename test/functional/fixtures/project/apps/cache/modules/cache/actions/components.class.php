<?php

/**
 * cache components.
 *
 * @package    project
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
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
