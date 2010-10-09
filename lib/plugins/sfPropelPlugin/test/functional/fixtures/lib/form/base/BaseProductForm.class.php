<?php

/**
 * Product form base class.
 *
 * @method Product getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseProductForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'price'            => new sfWidgetFormInputText(),
      'a_primary_string' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorPropelChoice(array('model' => 'Product', 'column' => 'id', 'required' => false)),
      'price'            => new sfValidatorNumber(array('required' => false)),
      'a_primary_string' => new sfValidatorString(array('max_length' => 64, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('product[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Product';
  }

  public function getI18nModelName()
  {
    return 'ProductI18n';
  }

  public function getI18nFormClass()
  {
    return 'ProductI18nForm';
  }

}
