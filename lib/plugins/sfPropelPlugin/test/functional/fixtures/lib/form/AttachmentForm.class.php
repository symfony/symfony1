<?php

/**
 * Attachment form.
 *
 * @package    form
 * @subpackage attachment
 * @version    SVN: $Id: AttachmentForm.class.php 8645 2008-04-27 15:37:17Z fabien $
 */
class AttachmentForm extends BaseAttachmentForm
{
  public function configure()
  {
    $this->widgetSchema['file'] = new sfWidgetFormInputFile();

    $fileValidator = new sfValidatorFile();
    $fileValidator->setOption('mime_type_guessers', array());
    $this->validatorSchema['file'] = $fileValidator;
  }
}
