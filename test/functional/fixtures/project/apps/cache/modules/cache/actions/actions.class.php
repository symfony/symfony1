<?php

/**
 * cache actions.
 *
 * @package    project
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class cacheActions extends sfActions
{
  public function executeIndex()
  {
  }

  public function executePage()
  {
  }

  public function executeForward()
  {
    $this->forward('cache', 'page');
  }

  public function executeMulti()
  {
  }
}
