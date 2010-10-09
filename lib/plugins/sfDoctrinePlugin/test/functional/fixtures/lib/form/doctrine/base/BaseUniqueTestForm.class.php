<?php

/**
 * UniqueTest form base class.
 *
 * @package    form
 * @subpackage unique_test
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseUniqueTestForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'unique_test1' => new sfWidgetFormInput(),
      'unique_test2' => new sfWidgetFormInput(),
      'unique_test3' => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => 'UniqueTest', 'column' => 'id', 'required' => false)),
      'unique_test1' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'unique_test2' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'unique_test3' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'UniqueTest', 'column' => array('unique_test1'))),
        new sfValidatorDoctrineUnique(array('model' => 'UniqueTest', 'column' => array('unique_test1', 'unique_test2'))),
      ))
    );

    $this->widgetSchema->setNameFormat('unique_test[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UniqueTest';
  }

}