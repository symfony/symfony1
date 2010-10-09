<?php

/**
 * BlogArticle form base class.
 *
 * @method BlogArticle getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseBlogArticleForm extends ArticleForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('blog_article[%s]');
  }

  public function getModelName()
  {
    return 'BlogArticle';
  }

}
