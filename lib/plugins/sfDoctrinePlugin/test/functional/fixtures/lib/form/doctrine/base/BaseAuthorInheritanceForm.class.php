<?php

/**
 * AuthorInheritance form base class.
 *
 * @method AuthorInheritance getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
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
