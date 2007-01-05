<?php

/**
 * validation actions.
 *
 * @package    project
 * @subpackage validation
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class validationActions extends sfActions
{
  public function executeIndex()
  {
  }

  public function handleErrorIndex()
  {
    return sfView::SUCCESS;
  }

  public function executeGroup()
  {
  }

  public function handleErrorGroup()
  {
    return sfView::SUCCESS;
  }
}
