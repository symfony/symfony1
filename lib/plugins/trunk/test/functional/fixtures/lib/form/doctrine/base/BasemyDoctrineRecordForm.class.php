<?php

/**
 * myDoctrineRecord form base class.
 *
 * @package    form
 * @subpackage my_doctrine_record
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasemyDoctrineRecordForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id' => new sfValidatorDoctrineChoice(array('model' => 'myDoctrineRecord', 'column' => 'id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('my_doctrine_record[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'myDoctrineRecord';
  }

}