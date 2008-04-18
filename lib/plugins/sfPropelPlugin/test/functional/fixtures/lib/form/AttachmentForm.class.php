<?php

/**
 * Attachment form.
 *
 * @package    form
 * @subpackage attachment
 * @version    SVN: $Id$
 */
class AttachmentForm extends BaseAttachmentForm
{
  public function configure()
  {
    $this->widgetSchema['file'] = new sfWidgetFormInputFile();
    $this->validatorSchema['file'] = new sfValidatorFile();
  }
}
