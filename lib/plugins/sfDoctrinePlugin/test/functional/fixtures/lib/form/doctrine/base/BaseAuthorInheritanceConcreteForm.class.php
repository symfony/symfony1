<?php

/**
 * AuthorInheritanceConcrete form base class.
 *
 * @method AuthorInheritanceConcrete getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceConcreteForm extends AuthorForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['additional'] = new sfWidgetFormInputText();
    $this->validatorSchema['additional'] = new sfValidatorString(array('max_length' => 255, 'required' => false));

    $this->widgetSchema->setNameFormat('author_inheritance_concrete[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritanceConcrete';
  }

}
