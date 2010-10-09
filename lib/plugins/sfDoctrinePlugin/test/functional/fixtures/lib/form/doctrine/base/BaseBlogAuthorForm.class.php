<?php

/**
 * BlogAuthor form base class.
 *
 * @method BlogAuthor getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24051 2009-11-16 21:08:08Z Kris.Wallsmith $
 */
abstract class BaseBlogAuthorForm extends AuthorForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('blog_author[%s]');
  }

  public function getModelName()
  {
    return 'BlogAuthor';
  }

}
