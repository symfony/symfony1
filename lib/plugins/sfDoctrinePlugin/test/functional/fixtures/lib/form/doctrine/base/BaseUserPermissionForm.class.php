<?php

/**
 * UserPermission form base class.
 *
 * @package    form
 * @subpackage user_permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseUserPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'       => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'user_id'       => new sfValidatorDoctrineChoice(array('model' => 'UserPermission', 'column' => 'user_id', 'required' => false)),
      'permission_id' => new sfValidatorDoctrineChoice(array('model' => 'UserPermission', 'column' => 'permission_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('user_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserPermission';
  }

}