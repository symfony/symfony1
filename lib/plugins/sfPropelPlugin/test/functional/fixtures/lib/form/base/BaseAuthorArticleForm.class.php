<?php

/**
 * AuthorArticle form base class.
 *
 * @package    form
 * @subpackage author_article
 * @version    SVN: $Id$
 */
class BaseAuthorArticleForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'author_id'  => new sfWidgetFormSelect(array('choices' => new sfCallable(array($this, 'getAuthorChoices')))),
      'article_id' => new sfWidgetFormSelect(array('choices' => new sfCallable(array($this, 'getArticleChoices')))),
      'id'         => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'author_id'  => new sfValidatorChoice(array('choices' => new sfCallable(array($this, 'getAuthorIdentifierChoices')), 'required' => false)),
      'article_id' => new sfValidatorChoice(array('choices' => new sfCallable(array($this, 'getArticleIdentifierChoices')), 'required' => false)),
      'id'         => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('author_article[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AuthorArticle';
  }


  public function getAuthorIdentifierChoices()
  {
    return array_keys($this->getAuthorChoices());
  }

  public function getAuthorChoices()
  {
    if (!isset($this->AuthorChoices))
    {
      $this->AuthorChoices = array('' => '');
      foreach (AuthorPeer::doSelect(new Criteria(), $this->getConnection()) as $object)
      {
        $this->AuthorChoices[$object->getId()] = $object->__toString();
      }
    }

    return $this->AuthorChoices;
  }
  public function getArticleIdentifierChoices()
  {
    return array_keys($this->getArticleChoices());
  }

  public function getArticleChoices()
  {
    if (!isset($this->ArticleChoices))
    {
      $this->ArticleChoices = array('' => '');
      foreach (ArticlePeer::doSelect(new Criteria(), $this->getConnection()) as $object)
      {
        $this->ArticleChoices[$object->getId()] = $object->__toString();
      }
    }

    return $this->ArticleChoices;
  }

}
