<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * UniqueTest filter form base class.
 *
 * @package    filters
 * @subpackage UniqueTest *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseUniqueTestFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'unique_test1' => new sfWidgetFormFilterInput(),
      'unique_test2' => new sfWidgetFormFilterInput(),
      'unique_test3' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'unique_test1' => new sfValidatorPass(array('required' => false)),
      'unique_test2' => new sfValidatorPass(array('required' => false)),
      'unique_test3' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('unique_test_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

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
    );
  }
}