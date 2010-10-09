<?php

/**
 * BlogArticleTranslation form base class.
 *
 * @method BlogArticleTranslation getObject() Returns the current form's model object
 *
 * @package    symfony12
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseBlogArticleTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'title'       => new sfWidgetFormInputText(),
      'body'        => new sfWidgetFormInputText(),
      'test_column' => new sfWidgetFormInputText(),
      'lang'        => new sfWidgetFormInputHidden(),
      'slug'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'title'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'body'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'test_column' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'lang'        => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'lang', 'required' => false)),
      'slug'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'BlogArticleTranslation', 'column' => array('title'))),
        new sfValidatorDoctrineUnique(array('model' => 'BlogArticleTranslation', 'column' => array('slug', 'lang', 'title'))),
      ))
    );

    $this->widgetSchema->setNameFormat('blog_article_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BlogArticleTranslation';
  }

}
