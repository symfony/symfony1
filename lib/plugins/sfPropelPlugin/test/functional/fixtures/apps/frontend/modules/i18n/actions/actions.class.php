<?php

/**
 * i18n actions.
 *
 * @package    test
 * @subpackage i18n
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 8296 2008-04-04 16:16:30Z fabien $
 */
class i18nActions extends sfActions
{
  public function executeIndex()
  {
    $this->getUser()->setCulture('fr');

    $this->movies = MoviePeer::doSelect(new Criteria());
  }

  public function executeDefault()
  {
    $this->movies = MoviePeer::doSelect(new Criteria());

    $this->setTemplate('index');
  }
}
