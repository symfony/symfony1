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
      'category_id' => new sfWidgetFormSelect(array('choices' => new sfCallable(array($this, 'getCategoryChoices')))),
      'created_at'  => new sfWidgetFormDateTime(),
      'end_date'    => new sfWidgetFormDateTime(),
      'book_id'     => new sfWidgetFormSelect(array('choices' => new sfCallable(array($this, 'getBookChoices')))),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorInteger(array('required' => false)),
      'title'       => new sfValidatorString(),
      'body'        => new sfValidatorString(array('required' => false)),
      'online'      => new sfValidatorBoolean(array('required' => false)),
      'category_id' => new sfValidatorChoice(array('choices' => new sfCallable(array($this, 'getCategoryIdentifierChoices')))),
      'created_at'  => new sfValidatorDateTime(array('required' => false)),
      'end_date'    => new sfValidatorDateTime(array('required' => false)),
      'book_id'     => new sfValidatorChoice(array('choices' => new sfCallable(array($this, 'getBookIdentifierChoices')), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('article[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Article';
  }


  public function getCategoryIdentifierChoices()
  {
    return array_keys($this->getCategoryChoices());
  }

  public function getCategoryChoices()
  {
    if (!isset($this->CategoryChoices))
    {
      $this->CategoryChoices = array();
      foreach (CategoryPeer::doSelect(new Criteria(), $this->getConnection()) as $object)
      {
        $this->CategoryChoices[$object->getId()] = $object->__toString();
      }
    }

    return $this->CategoryChoices;
  }
  public function getBookIdentifierChoices()
  {
    return array_keys($this->getBookChoices());
  }

  public function getBookChoices()
  {
    if (!isset($this->BookChoices))
    {
      $this->BookChoices = array('' => '');
      foreach (BookPeer::doSelect(new Criteria(), $this->getConnection()) as $object)
      {
        $this->BookChoices[$object->getId()] = $object->__toString();
      }
    }

    return $this->BookChoices;
  }

}
