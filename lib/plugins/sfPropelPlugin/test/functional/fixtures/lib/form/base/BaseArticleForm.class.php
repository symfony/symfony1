<?php

/**
 * Article form base class.
 *
 * @package    form
 * @subpackage article
 * @version    SVN: $Id$
 */
class BaseArticleForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'title'       => new sfWidgetFormInput(),
      'body'        => new sfWidgetFormTextarea(),
      'online'      => new sfWidgetFormInputCheckbox(),
      'category_id' => new sfWidgetFormPropelSelect(array('model' => 'Category', 'add_empty' => false)),
      'created_at'  => new sfWidgetFormDateTime(),
      'end_date'    => new sfWidgetFormDateTime(),
      'book_id'     => new sfWidgetFormPropelSelect(array('model' => 'Book', 'add_empty' => true)),
      'author_list' => new sfWidgetFormPropelSelectMany(array('model' => 'Author')),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorInteger(array('required' => false)),
      'title'       => new sfValidatorString(),
      'body'        => new sfValidatorString(array('required' => false)),
      'online'      => new sfValidatorBoolean(array('required' => false)),
      'category_id' => new sfValidatorPropelChoice(array('model' => 'Category')),
      'created_at'  => new sfValidatorDateTime(array('required' => false)),
      'end_date'    => new sfValidatorDateTime(array('required' => false)),
      'book_id'     => new sfValidatorPropelChoice(array('model' => 'Book', 'required' => false)),
      'author_list' => new sfValidatorPropelChoiceMany(array('model' => 'Author', 'required' => false)),
    ));

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

    if (isset($this->widgetSchema['author_list']))
    {
      $values = array();
      foreach ($this->object->getAuthorArticles() as $obj)
      {
        $values[] = $obj->getAuthorId();
      }

      $this->setDefault('author_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveAuthorList($con);
  }

  public function saveAuthorList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['author_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(AuthorArticlePeer::ARTICLE_ID, $this->object->getPrimaryKey());
    AuthorArticlePeer::doDelete($c, $con);

    $values = $this->getValues();
    if (is_array($values['author_list']))
    {
      foreach ($values['author_list'] as $value)
      {
        $obj = new AuthorArticle();
        $obj->setArticleId($this->object->getPrimaryKey());
        $obj->setAuthorId($value);
        $obj->save();
      }
    }
  }

}
