<?php

/**
 * Author filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAuthorFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name' => new sfWidgetFormFilterInput(),
      'type' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name' => new sfValidatorPass(array('required' => false)),
      'type' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('author_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Author';
  }

  public function getFields()
  {
    return array(
      'id'   => 'Number',
      'name' => 'Text',
      'type' => 'Text',
    );
  }
}
