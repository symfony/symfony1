<?php

/**
 * Permission form base class.
 *
 * @package    form
 * @subpackage permission
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'name'        => new sfWidgetFormInput(),
      'users_list'  => new sfWidgetFormDoctrineChoiceMany(array('model' => 'User')),
      'groups_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Group')),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => 'Permission', 'column' => 'id', 'required' => false)),
      'name'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'users_list'  => new sfValidatorDoctrineChoiceMany(array('model' => 'User', 'required' => false)),
      'groups_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Group', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Permission';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['users_list']))
    {
      $this->setDefault('users_list', $this->object->Users->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveUsersList($con);
    $this->saveGroupsList($con);
  }

  public function saveUsersList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Users', array());

    $values = $this->getValue('users_list');
    if (is_array($values))
    {
      $this->object->link('Users', $values);
    }
  }

  public function saveGroupsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->object->unlink('Groups', array());

    $values = $this->getValue('groups_list');
    if (is_array($values))
    {
      $this->object->link('Groups', $values);
    }
  }

}