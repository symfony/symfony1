<?php

/**
 * Author form base class.
 *
 * @package    form
 * @subpackage author
 * @version    SVN: $Id$
 */
class BaseAuthorForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgetSchema(new sfWidgetFormSchema(array(
      'id'   => new sfWidgetFormInputHidden(),
      'name' => new sfWidgetFormInput(),
    )));

    $this->setValidatorSchema(new sfValidatorSchema(array(
      'id'   => new sfValidatorInteger(array('required' => false)),
      'name' => new sfValidatorString(array('required' => false)),
    )));

    $this->widgetSchema->setNameFormat('author[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Author';
  }



}
