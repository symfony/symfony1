<?php

/**
 * UniqueTest form base class.
 *
 * @method UniqueTest getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseUniqueTestForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'unique_test1' => new sfWidgetFormInputText(),
      'unique_test2' => new sfWidgetFormInputText(),
      'unique_test3' => new sfWidgetFormInputText(),
      'unique_test4' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'unique_test1' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'unique_test2' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'unique_test3' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'unique_test4' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'UniqueTest', 'column' => array('unique_test1'))),
        new sfValidatorDoctrineUnique(array('model' => 'UniqueTest', 'column' => array('unique_test1', 'unique_test2'))),
        new sfValidatorDoctrineUnique(array('model' => 'UniqueTest', 'column' => array('unique_test4'))),
      ))
    );

    $this->widgetSchema->setNameFormat('unique_test[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UniqueTest';
  }

}
