<?php

/**
 * AuthorInheritance filter form base class.
 *
 * @package    symfony12
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceFormFilter extends AuthorFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('author_inheritance_filters[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritance';
  }
}
