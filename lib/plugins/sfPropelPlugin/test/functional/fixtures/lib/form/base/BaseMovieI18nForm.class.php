<?php

/**
 * MovieI18n form base class.
 *
 * @method MovieI18n getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
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
      'culture' => new sfValidatorPropelChoice(array('model' => 'MovieI18n', 'column' => 'culture', 'required' => false)),
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
