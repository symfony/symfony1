<?php

/**
 * CamelCase form base class.
 *
 * @package    form
 * @subpackage camel_case
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseCamelCaseForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'article_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'Article', 'add_empty' => true)),
      'testCamelCase' => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => 'CamelCase', 'column' => 'id', 'required' => false)),
      'article_id'    => new sfValidatorDoctrineChoice(array('model' => 'Article', 'required' => false)),
      'testCamelCase' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('camel_case[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CamelCase';
  }

}
