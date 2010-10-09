<?php

/**
 * Subscription filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseSubscriptionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'   => new sfWidgetFormFilterInput(),
      'status' => new sfWidgetFormChoice(array('choices' => array('' => '', 'New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'))),
    ));

    $this->setValidators(array(
      'name'   => new sfValidatorPass(array('required' => false)),
      'status' => new sfValidatorChoice(array('required' => false, 'choices' => array('New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'))),
    ));

    $this->widgetSchema->setNameFormat('subscription_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Subscription';
  }

  public function getFields()
  {
    return array(
      'id'     => 'Number',
      'name'   => 'Text',
      'status' => 'Enum',
    );
  }
}
