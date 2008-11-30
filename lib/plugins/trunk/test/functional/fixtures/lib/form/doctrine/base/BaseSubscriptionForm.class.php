<?php

/**
 * Subscription form base class.
 *
 * @package    form
 * @subpackage subscription
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseSubscriptionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'     => new sfWidgetFormInputHidden(),
      'name'   => new sfWidgetFormInput(),
      'status' => new sfWidgetFormChoice(array('choices' => array('New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'))),
    ));

    $this->setValidators(array(
      'id'     => new sfValidatorDoctrineChoice(array('model' => 'Subscription', 'column' => 'id', 'required' => false)),
      'name'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'status' => new sfValidatorChoice(array('choices' => array('New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('subscription[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Subscription';
  }

}