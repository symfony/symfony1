<?php

/**
 * Attachment form.
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: AttachmentForm.class.php 24068 2009-11-17 06:39:35Z Kris.Wallsmith $
 */
class AttachmentForm extends BaseAttachmentForm
{
  const
    TEST_GENERATED_FILENAME = 'test123';

  public function configure()
  {
    $this->widgetSchema['file_path'] = new sfWidgetFormInputFile();
    $this->validatorSchema['file_path'] = new sfValidatorFile(array(
      'path' => sfConfig::get('sf_cache_dir'),
      'mime_type_guessers' => array(),
    ));
  }

  protected function generateFilePathFilename()
  {
    return self::TEST_GENERATED_FILENAME;
  }
}
