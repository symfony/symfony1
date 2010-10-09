<?php

/**
 * AuthorInheritance form base class.
 *
 * @method AuthorInheritance getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceForm extends AuthorForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('author_inheritance[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritance';
  }

}
