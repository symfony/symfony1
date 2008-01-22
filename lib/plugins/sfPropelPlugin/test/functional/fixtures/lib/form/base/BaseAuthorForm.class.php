<?php

/**
 * Author form base class.
 *
 * @package    form
 * @subpackage author
 * @version    SVN: $Id$
 */
class BaseAuthorForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInput(),
      'article_list' => new sfWidgetFormPropelSelectMany(array('model' => 'Article')),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorInteger(array('required' => false)),
      'name'         => new sfValidatorString(array('required' => false)),
      'article_list' => new sfValidatorPropelChoiceMany(array('model' => 'Article', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('author[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Author';
  }


  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['article_list']))
    {
      $values = array();
      foreach ($this->object->getAuthorArticles() as $obj)
      {
        $values[] = $obj->getArticleId();
      }

      $this->setDefault('article_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveArticleList($con);
  }

  public function saveArticleList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['article_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(AuthorArticlePeer::AUTHOR_ID, $this->object->getPrimaryKey());
    AuthorArticlePeer::doDelete($c, $con);

    $values = $this->getValues();
    if (is_array($values['article_list']))
    {
      foreach ($values['article_list'] as $value)
      {
        $obj = new AuthorArticle();
        $obj->setAuthorId($this->object->getPrimaryKey());
        $obj->setArticleId($value);
        $obj->save();
      }
    }
  }

}
