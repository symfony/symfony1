<?php

/**
 * Book form base class.
 *
 * @package    form
 * @subpackage book
 * @version    SVN: $Id$
 */
class BaseBookForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'   => new sfWidgetFormInputHidden(),
      'name' => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'   => new sfValidatorInteger(array('required' => false)),
      'name' => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('book[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Book';
  }


}
