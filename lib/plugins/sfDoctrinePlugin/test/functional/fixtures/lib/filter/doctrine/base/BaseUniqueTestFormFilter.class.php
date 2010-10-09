<?php

/**
 * UniqueTest filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseUniqueTestFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'unique_test1' => new sfWidgetFormFilterInput(),
      'unique_test2' => new sfWidgetFormFilterInput(),
      'unique_test3' => new sfWidgetFormFilterInput(),
      'unique_test4' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'unique_test1' => new sfValidatorPass(array('required' => false)),
      'unique_test2' => new sfValidatorPass(array('required' => false)),
      'unique_test3' => new sfValidatorPass(array('required' => false)),
      'unique_test4' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('unique_test_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UniqueTest';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'unique_test1' => 'Text',
      'unique_test2' => 'Text',
      'unique_test3' => 'Text',
      'unique_test4' => 'Text',
    );
  }
}
