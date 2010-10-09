<?php

/**
 * User form base class.
 *
 * @method User getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'username'         => new sfWidgetFormInputText(),
      'password'         => new sfWidgetFormInputText(),
      'test'             => new sfWidgetFormInputText(),
      'groups_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Group')),
      'permissions_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Permission')),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'username'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'password'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'test'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'groups_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Group', 'required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Permission', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'User', 'column' => array('username')))
    );

    $this->widgetSchema->setNameFormat('user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'User';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['permissions_list']))
    {
      $this->setDefault('permissions_list', $this->object->Permissions->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveGroupsList($con);
    $this->savePermissionsList($con);

    parent::doSave($con);
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

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Groups->getPrimaryKeys();
    $values = $this->getValue('groups_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Groups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Groups', array_values($link));
    }
  }

  public function savePermissionsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['permissions_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Permissions->getPrimaryKeys();
    $values = $this->getValue('permissions_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Permissions', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Permissions', array_values($link));
    }
  }

}
