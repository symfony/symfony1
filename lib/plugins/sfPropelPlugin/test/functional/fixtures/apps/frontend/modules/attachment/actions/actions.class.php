<?php

/**
 * attachment actions.
 *
 * @package    test
 * @subpackage attachment
 * @author     Your name here
 * @version    SVN: $Id$
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
        $file = $this->form->getValue('file');
        $file->save(sfConfig::get('sf_cache_dir').'/uploaded'.$file->getExtension($file->getOriginalExtension()));

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
