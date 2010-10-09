<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * Group filter form base class.
 *
 * @package    filters
 * @subpackage Group *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseGroupFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'             => new sfWidgetFormFilterInput(),
      'permissions_list' => new sfWidgetFormDoctrineChoiceMany(array('model' => 'Permission')),
      'users_list'       => new sfWidgetFormDoctrineChoiceMany(array('model' => 'User')),
    ));

    $this->setValidators(array(
      'name'             => new sfValidatorPass(array('required' => false)),
      'permissions_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Permission', 'required' => false)),
      'users_list'       => new sfValidatorDoctrineChoiceMany(array('model' => 'User', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('group_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

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