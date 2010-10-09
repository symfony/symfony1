<?php

/**
 * Attachment form base class.
 *
 * @method Attachment getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAttachmentForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'article_id' => new sfWidgetFormPropelChoice(array('model' => 'Article', 'add_empty' => true)),
      'name'       => new sfWidgetFormInputText(),
      'file'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorPropelChoice(array('model' => 'Attachment', 'column' => 'id', 'required' => false)),
      'article_id' => new sfValidatorPropelChoice(array('model' => 'Article', 'column' => 'id', 'required' => false)),
      'name'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'file'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('attachment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Attachment';
  }


}
