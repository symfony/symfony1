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
      'author_id'  => new sfWidgetFormInputHidden(),
      'article_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'author_id'  => new sfValidatorPropelChoice(array('model' => 'Author', 'required' => false)),
      'article_id' => new sfValidatorPropelChoice(array('model' => 'Article', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('author_article[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AuthorArticle';
  }


}
