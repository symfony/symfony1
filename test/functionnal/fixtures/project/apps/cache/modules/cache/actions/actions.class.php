<?php

/**
 * cache actions.
 *
 * @package    project
 * @subpackage cache
 * @author     Your name here
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
