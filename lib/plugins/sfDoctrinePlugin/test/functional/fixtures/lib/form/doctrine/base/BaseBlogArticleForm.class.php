<?php

/**
 * BlogArticle form base class.
 *
 * @method BlogArticle getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
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
