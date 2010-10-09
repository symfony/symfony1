<?php

/**
 * AuthorInheritanceConcrete filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceConcreteFormFilter extends AuthorFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['additional'] = new sfWidgetFormFilterInput();
    $this->validatorSchema['additional'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema->setNameFormat('author_inheritance_concrete_filters[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritanceConcrete';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'additional' => 'Text',
    ));
  }
}
