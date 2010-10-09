<?php

/**
 * choice actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage choice
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 15627 2009-02-19 13:35:10Z Kris.Wallsmith $
 */
class choiceActions extends sfActions
{
  public function executeArticle($request)
  {
    $this->form = new ArticleForm();

    if ($request->getParameter('impossible_validator'))
    {
      $criteria = new Criteria();
      $criteria->add(CategoryPeer::ID, null, Criteria::ISNULL);

      $validators = $this->form->getValidatorSchema();
      $validators['category_id']->setOption('criteria', $criteria);
      $validators['category_id']->setMessage('invalid', 'Invalid category.');
    }

    if ($request->getParameter('impossible_validator_many'))
    {
      $criteria = new Criteria();
      $criteria->add(AuthorPeer::ID, null, Criteria::ISNULL);

      $validators = $this->form->getValidatorSchema();
      $validators['author_article_list']->setOption('criteria', $criteria);
      $validators['author_article_list']->setMessage('invalid', 'Invalid author.');
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('article'));

      if ($this->form->isValid())
      {
        $this->form->save();

        $this->redirect('choice/ok');
      }
    }
  }

  public function executeOk()
  {
    return $this->renderText('ok');
  }
}
