<?php

/**
 * attachment actions.
 *
 * @package    test
 * @subpackage attachment
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 11445 2008-09-11 14:19:28Z fabien $
 */
class attachmentActions extends sfActions
{
  public function executeIndex($request)
  {
    $this->form = new AttachmentForm();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('attachment'), $request->getFiles('attachment'));

      if ($this->form->isValid())
      {
        $this->form->save();

        $this->redirect('attachment/ok');
      }
    }
  }

  public function executeOk()
  {
    return $this->renderText('ok');
  }
}
