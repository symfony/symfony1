<?php

/**
 * MovieI18n form base class.
 *
 * @method MovieI18n getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 */
abstract class BaseMovieI18nForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'      => new sfWidgetFormInputHidden(),
      'culture' => new sfWidgetFormInputHidden(),
      'title'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'      => new sfValidatorPropelChoice(array('model' => 'Movie', 'column' => 'id', 'required' => false)),
      'culture' => new sfValidatorChoice(array('choices' => array($this->getObject()->getCulture()), 'empty_value' => $this->getObject()->getCulture(), 'required' => false)),
      'title'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorPropelUnique(array('model' => 'MovieI18n', 'column' => array('title')))
    );

    $this->widgetSchema->setNameFormat('movie_i18n[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'MovieI18n';
  }


}
