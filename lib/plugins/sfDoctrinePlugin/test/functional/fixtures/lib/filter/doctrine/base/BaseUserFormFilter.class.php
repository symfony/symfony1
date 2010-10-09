<?php

/**
 * User filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseUserFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'username'         => new sfWidgetFormFilterInput(),
      'password'         => new sfWidgetFormFilterInput(),
      'test'             => new sfWidgetFormFilterInput(),
      'groups_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Group')),
      'permissions_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Permission')),
    ));

    $this->setValidators(array(
      'username'         => new sfValidatorPass(array('required' => false)),
      'password'         => new sfValidatorPass(array('required' => false)),
      'test'             => new sfValidatorPass(array('required' => false)),
      'groups_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Group', 'required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Permission', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('user_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.UserGroup UserGroup')
          ->andWhereIn('UserGroup.group_id', $values);
  }

  public function addPermissionsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.UserPermission UserPermission')
          ->andWhereIn('UserPermission.permission_id', $values);
  }

  public function getModelName()
  {
    return 'User';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'username'         => 'Text',
      'password'         => 'Text',
      'test'             => 'Text',
      'groups_list'      => 'ManyKey',
      'permissions_list' => 'ManyKey',
    );
  }
}
