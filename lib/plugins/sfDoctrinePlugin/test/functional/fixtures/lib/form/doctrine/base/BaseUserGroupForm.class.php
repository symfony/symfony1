<?php

/**
 * UserGroup form base class.
 *
 * @package    form
 * @subpackage user_group
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseUserGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'  => new sfWidgetFormInputHidden(),
      'group_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'user_id'  => new sfValidatorDoctrineChoice(array('model' => 'UserGroup', 'column' => 'user_id', 'required' => false)),
      'group_id' => new sfValidatorDoctrineChoice(array('model' => 'UserGroup', 'column' => 'group_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('user_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserGroup';
  }

}