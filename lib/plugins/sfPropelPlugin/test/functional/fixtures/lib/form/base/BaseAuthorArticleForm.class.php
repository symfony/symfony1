<?php

/**
 * AuthorArticle form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 12815 2008-11-09 10:43:58Z fabien $
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
      'author_id'  => new sfValidatorPropelChoice(array('model' => 'Author', 'column' => 'id', 'required' => false)),
      'article_id' => new sfValidatorPropelChoice(array('model' => 'Article', 'column' => 'id', 'required' => false)),
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
