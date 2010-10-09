<?php

/**
 * attachment actions.
 *
 * @package    symfony12
 * @subpackage attachment
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 24068 2009-11-17 06:39:35Z Kris.Wallsmith $
 */
class attachmentActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new AttachmentForm();
    unset($this->form['id']);

    if (
      $request->isMethod('post')
      &&
      $this->form->bindAndSave(
        $request->getParameter($this->form->getName()),
        $request->getFiles($this->form->getName())
      )
    )
    {
      return sfView::SUCCESS;
    }

    return sfView::INPUT;
  }
}
