<?php

/**
 * Profile form base class.
 *
 * @package    form
 * @subpackage profile
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'user_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'User', 'add_empty' => true)),
      'first_name' => new sfWidgetFormInput(),
      'last_name'  => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => 'Profile', 'column' => 'id', 'required' => false)),
      'user_id'    => new sfValidatorDoctrineChoice(array('model' => 'User', 'required' => false)),
      'first_name' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'last_name'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Profile';
  }

}