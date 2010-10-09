<?php

/**
 * Author form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 16976 2009-04-04 12:47:44Z fabien $
 */
class BaseAuthorForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'name'                => new sfWidgetFormInput(),
      'author_article_list' => new sfWidgetFormPropelChoiceMany(array('model' => 'Article')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorPropelChoice(array('model' => 'Author', 'column' => 'id', 'required' => false)),
      'name'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'author_article_list' => new sfValidatorPropelChoiceMany(array('model' => 'Article', 'required' => false)),
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

    if (isset($this->widgetSchema['author_article_list']))
    {
      $values = array();
      foreach ($this->object->getAuthorArticles() as $obj)
      {
        $values[] = $obj->getArticleId();
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

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(AuthorArticlePeer::AUTHOR_ID, $this->object->getPrimaryKey());
    AuthorArticlePeer::doDelete($c, $con);

    $values = $this->getValue('author_article_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new AuthorArticle();
        $obj->setAuthorId($this->object->getPrimaryKey());
        $obj->setArticleId($value);
        $obj->save();
      }
    }
  }

}
