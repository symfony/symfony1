<?php

/**
 * Movie form base class.
 *
 * @method Movie getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'       => new sfWidgetFormInputHidden(),
      'director' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'       => new sfValidatorPropelChoice(array('model' => 'Movie', 'column' => 'id', 'required' => false)),
      'director' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Movie';
  }

  public function getI18nModelName()
  {
    return 'MovieI18n';
  }

  public function getI18nFormClass()
  {
    return 'MovieI18nForm';
  }

}
