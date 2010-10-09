<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * CamelCase filter form base class.
 *
 * @package    filters
 * @subpackage CamelCase *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseCamelCaseFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'article_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'Article', 'add_empty' => true)),
      'testCamelCase' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'article_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Article', 'column' => 'id')),
      'testCamelCase' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('camel_case_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CamelCase';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'article_id'    => 'ForeignKey',
      'testCamelCase' => 'Text',
    );
  }
}