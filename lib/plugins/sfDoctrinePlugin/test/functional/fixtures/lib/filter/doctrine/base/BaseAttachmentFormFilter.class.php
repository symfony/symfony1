<?php

/**
 * Attachment filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAttachmentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'file_path' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'file_path' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('attachment_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Attachment';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'file_path' => 'Text',
    );
  }
}
