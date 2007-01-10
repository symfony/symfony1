<?php

/**
 * presentation actions.
 *
 * @package    project
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class presentationActions extends sfActions
{
  public function executeIndex()
  {
    $this->foo1 = $this->getPresentationFor('presentation', 'foo');
    $this->foo2 = $this->getController()->getPresentationFor('presentation', 'foo');
  }

  public function executeFoo()
  {
    $this->setLayout(false);
  }
}
