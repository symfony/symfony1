<?php

/**
 * Article form base class.
 *
 * @method Article getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseArticleForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'title'               => new sfWidgetFormInputText(),
      'body'                => new sfWidgetFormTextarea(),
      'Online'              => new sfWidgetFormInputCheckbox(),
      'excerpt'             => new sfWidgetFormInputText(),
      'category_id'         => new sfWidgetFormPropelChoice(array('model' => 'Category', 'add_empty' => false)),
      'created_at'          => new sfWidgetFormDateTime(),
      'end_date'            => new sfWidgetFormDateTime(),
      'book_id'             => new sfWidgetFormPropelChoice(array('model' => 'Book', 'add_empty' => true)),
      'author_article_list' => new sfWidgetFormPropelChoice(array('multiple' => true, 'model' => 'Author')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorPropelChoice(array('model' => 'Article', 'column' => 'id', 'required' => false)),
      'title'               => new sfValidatorString(array('max_length' => 255)),
      'body'                => new sfValidatorString(array('required' => false)),
      'Online'              => new sfValidatorBoolean(array('required' => false)),
      'excerpt'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'category_id'         => new sfValidatorPropelChoice(array('model' => 'Category', 'column' => 'id')),
      'created_at'          => new sfValidatorDateTime(array('required' => false)),
      'end_date'            => new sfValidatorDateTime(array('required' => false)),
      'book_id'             => new sfValidatorPropelChoice(array('model' => 'Book', 'column' => 'id', 'required' => false)),
      'author_article_list' => new sfValidatorPropelChoice(array('multiple' => true, 'model' => 'Author', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorPropelUnique(array('model' => 'Article', 'column' => array('title', 'category_id')))
    );

    $this->widgetSchema->setNameFormat('article[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Article';
  }


  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['author_article_list']))
    {
      $values = array();
      foreach ($this->object->getAuthorArticles() as $obj)
      {
        $values[] = $obj->getAuthorId();
      }

      $this->setDefault('author_article_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveAuthorArticleList($con);
  }

  public function saveAuthorArticleList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['author_article_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(AuthorArticlePeer::ARTICLE_ID, $this->object->getPrimaryKey());
    AuthorArticlePeer::doDelete($c, $con);

    $values = $this->getValue('author_article_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new AuthorArticle();
        $obj->setArticleId($this->object->getPrimaryKey());
        $obj->setAuthorId($value);
        $obj->save();
      }
    }
  }

}
