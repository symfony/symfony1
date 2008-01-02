<?php

/**
 * Category form base class.
 *
 * @package    form
 * @subpackage category
 * @version    SVN: $Id$
 */
class BaseCategoryForm extends BaseFormPropel
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

    $this->widgetSchema->setNameFormat('category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Category';
  }



}
