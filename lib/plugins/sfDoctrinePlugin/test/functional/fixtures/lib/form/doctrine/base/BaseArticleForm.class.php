<?php

/**
 * Article form base class.
 *
 * @method Article getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseArticleForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'author_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'add_empty' => true)),
      'is_on_homepage' => new sfWidgetFormInputCheckbox(),
      'type'           => new sfWidgetFormInputText(),
      'created_at'     => new sfWidgetFormDateTime(),
      'updated_at'     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'author_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Author'), 'required' => false)),
      'is_on_homepage' => new sfValidatorBoolean(array('required' => false)),
      'type'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'     => new sfValidatorDateTime(),
      'updated_at'     => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('article[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Article';
  }

}
