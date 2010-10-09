<?php

/**
 * ArticleTranslation form base class.
 *
 * @package    form
 * @subpackage article_translation
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseArticleTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'    => new sfWidgetFormInputHidden(),
      'title' => new sfWidgetFormInput(),
      'body'  => new sfWidgetFormInput(),
      'lang'  => new sfWidgetFormInputHidden(),
      'slug'  => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'    => new sfValidatorDoctrineChoice(array('model' => 'ArticleTranslation', 'column' => 'id', 'required' => false)),
      'title' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'lang'  => new sfValidatorDoctrineChoice(array('model' => 'ArticleTranslation', 'column' => 'lang', 'required' => false)),
      'slug'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ArticleTranslation', 'column' => array('slug', 'lang', 'title')))
    );

    $this->widgetSchema->setNameFormat('article_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ArticleTranslation';
  }

}