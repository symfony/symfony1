<?php

/**
 * AuthorInheritance form base class.
 *
 * @method AuthorInheritance getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24051 2009-11-16 21:08:08Z Kris.Wallsmith $
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
