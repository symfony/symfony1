<?php

/**
 * Group filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseGroupFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'             => new sfWidgetFormFilterInput(),
      'permissions_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Permission')),
      'users_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'User')),
    ));

    $this->setValidators(array(
      'name'             => new sfValidatorPass(array('required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Permission', 'required' => false)),
      'users_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'User', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('group_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
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

    $query->leftJoin('r.GroupPermission GroupPermission')
          ->andWhereIn('GroupPermission.permission_id', $values);
  }

  public function addUsersListColumnQuery(Doctrine_Query $query, $field, $values)
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
          ->andWhereIn('UserGroup.user_id', $values);
  }

  public function getModelName()
  {
    return 'Group';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'name'             => 'Text',
      'permissions_list' => 'ManyKey',
      'users_list'       => 'ManyKey',
    );
  }
}
