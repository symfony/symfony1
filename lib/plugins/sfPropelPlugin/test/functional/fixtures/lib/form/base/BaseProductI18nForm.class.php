<?php

/**
 * ProductI18n form base class.
 *
 * @method ProductI18n getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseProductI18nForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'      => new sfWidgetFormInputHidden(),
      'culture' => new sfWidgetFormInputHidden(),
      'name'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'      => new sfValidatorPropelChoice(array('model' => 'Product', 'column' => 'id', 'required' => false)),
      'culture' => new sfValidatorPropelChoice(array('model' => 'ProductI18n', 'column' => 'culture', 'required' => false)),
      'name'    => new sfValidatorString(array('max_length' => 50, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('product_i18n[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProductI18n';
  }


}
