<?php

/**
 * autoValidation actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage autoValidation
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 9926 2008-06-27 12:25:54Z noel $
 */
class autoValidationActions extends sfActions
{
  public function executeIndex()
  {
    return $this->forward('validation', 'list');
  }

  public function executeList()
  {
    $this->processSort();

    $this->processFilters();


    // pager
    $this->pager = new sfPropelPager('Article', 20);
    $c = new Criteria();
    $this->addSortCriteria($c);
    $this->addFiltersCriteria($c);
    $this->pager->setCriteria($c);
    $this->pager->setPage($this->getRequestParameter('page', $this->getUser()->getAttribute('page', 1, 'sf_admin/article')));
    $this->pager->init();
    // save page
    if ($this->getRequestParameter('page')) {
        $this->getUser()->setAttribute('page', $this->getRequestParameter('page'), 'sf_admin/article');
    }
  }

  public function executeCreate()
  {
    return $this->forward('validation', 'edit');
  }

  public function executeSave()
  {
    return $this->forward('validation', 'edit');
  }


  public function executeDeleteSelected()
  {
    $this->selectedItems = $this->getRequestParameter('sf_admin_batch_selection', array());

    try
    {
      foreach (ArticlePeer::retrieveByPks($this->selectedItems) as $object)
      {
        $object->delete();
      }
    }
    catch (PropelException $e)
    {
      $this->getRequest()->setError('delete', 'Could not delete the selected Articles. Make sure they do not have any associated items.');
      return $this->forward('validation', 'list');
    }

    return $this->redirect('validation/list');
  }

  public function executeEdit()
  {
    $this->article = $this->getArticleOrCreate();

    if ($this->getRequest()->isMethod('post'))
    {
      $this->updateArticleFromRequest();

      try
      {
        $this->saveArticle($this->article);
      }
      catch (PropelException $e)
      {
        $this->getRequest()->setError('edit', 'Could not save the edited Articles.');
        return $this->forward('validation', 'list');
      }

      $this->getUser()->setFlash('notice', 'Your modifications have been saved');

      if ($this->getRequestParameter('save_and_add'))
      {
        return $this->redirect('validation/create');
      }
      else if ($this->getRequestParameter('save_and_list'))
      {
        return $this->redirect('validation/list');
      }
      else
      {
        return $this->redirect('validation/edit?id='.$this->article->getId());
      }
    }
    else
    {
      $this->labels = $this->getLabels();
    }
  }

  public function executeDelete()
  {
    $this->article = ArticlePeer::retrieveByPk($this->getRequestParameter('id'));
    $this->forward404Unless($this->article);

    try
    {
      $this->deleteArticle($this->article);
    }
    catch (PropelException $e)
    {
      $this->getRequest()->setError('delete', 'Could not delete the selected Article. Make sure it does not have any associated items.');
      return $this->forward('validation', 'list');
    }

    return $this->redirect('validation/list');
  }

  public function handleErrorEdit()
  {
    $this->preExecute();
    $this->article = $this->getArticleOrCreate();
    $this->updateArticleFromRequest();

    $this->labels = $this->getLabels();

    return sfView::SUCCESS;
  }

  protected function saveArticle($article)
  {
    $article->save();

  }

  protected function deleteArticle($article)
  {
    $article->delete();
  }

  protected function updateArticleFromRequest()
  {
    $article = $this->getRequestParameter('article');

    if (isset($article['title']))
    {
      $this->article->setTitle($article['title']);
    }
    if (isset($article['body']))
    {
      $this->article->setBody($article['body']);
    }
    $this->article->setOnline(isset($article['online']) ? $article['online'] : 0);
    if (isset($article['excerpt']))
    {
      $this->article->setExcerpt($article['excerpt']);
    }
    if (isset($article['category_id']))
    {
    $this->article->setCategoryId($article['category_id'] ? $article['category_id'] : null);
    }
    if (isset($article['created_at']))
    {
      if ($article['created_at'])
      {
        try
        {
          $dateFormat = new sfDateFormat($this->getUser()->getCulture());
                              if (!is_array($article['created_at']))
          {
            $value = $dateFormat->format($article['created_at'], 'I', $dateFormat->getInputPattern('g'));
          }
          else
          {
            $value_array = $article['created_at'];
            $value = $value_array['year'].'-'.$value_array['month'].'-'.$value_array['day'].(isset($value_array['hour']) ? ' '.$value_array['hour'].':'.$value_array['minute'].(isset($value_array['second']) ? ':'.$value_array['second'] : '') : '');
          }
          $this->article->setCreatedAt($value);
        }
        catch (sfException $e)
        {
          // not a date
        }
      }
      else
      {
        $this->article->setCreatedAt(null);
      }
    }
    if (isset($article['end_date']))
    {
      if ($article['end_date'])
      {
        try
        {
          $dateFormat = new sfDateFormat($this->getUser()->getCulture());
                              if (!is_array($article['end_date']))
          {
            $value = $dateFormat->format($article['end_date'], 'I', $dateFormat->getInputPattern('g'));
          }
          else
          {
            $value_array = $article['end_date'];
            $value = $value_array['year'].'-'.$value_array['month'].'-'.$value_array['day'].(isset($value_array['hour']) ? ' '.$value_array['hour'].':'.$value_array['minute'].(isset($value_array['second']) ? ':'.$value_array['second'] : '') : '');
          }
          $this->article->setEndDate($value);
        }
        catch (sfException $e)
        {
          // not a date
        }
      }
      else
      {
        $this->article->setEndDate(null);
      }
    }
    if (isset($article['book_id']))
    {
    $this->article->setBookId($article['book_id'] ? $article['book_id'] : null);
    }
  }

  protected function getArticleOrCreate($id = 'id')
  {
    if ($this->getRequestParameter($id) === ''
     || $this->getRequestParameter($id) === null)
    {
      $article = new Article();
    }
    else
    {
      $article = ArticlePeer::retrieveByPk($this->getRequestParameter($id));

      $this->forward404Unless($article);
    }

    return $article;
  }

  protected function processFilters()
  {
  }

  protected function processSort()
  {
    if ($this->getRequestParameter('sort'))
    {
      $this->getUser()->setAttribute('sort', $this->getRequestParameter('sort'), 'sf_admin/article/sort');
      $this->getUser()->setAttribute('type', $this->getRequestParameter('type', 'asc'), 'sf_admin/article/sort');
    }

    if (!$this->getUser()->getAttribute('sort', null, 'sf_admin/article/sort'))
    {
    }
  }

  protected function addFiltersCriteria($c)
  {
  }

  protected function addSortCriteria($c)
  {
    if ($sort_column = $this->getUser()->getAttribute('sort', null, 'sf_admin/article/sort'))
    {
      $sort_column = sfInflector::camelize(strtolower($sort_column));
      $sort_column = ArticlePeer::translateFieldName($sort_column, BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME);
      if ($this->getUser()->getAttribute('type', null, 'sf_admin/article/sort') == 'asc')
      {
        $c->addAscendingOrderByColumn($sort_column);
      }
      else
      {
        $c->addDescendingOrderByColumn($sort_column);
      }
    }
  }

  protected function getLabels()
  {
    return array(
      'article{id}' => 'Id:',
      'article{title}' => 'Title:',
      'article{body}' => 'Body:',
      'article{online}' => 'Online:',
      'article{excerpt}' => 'Excerpt:',
      'article{category_id}' => 'Category:',
      'article{created_at}' => 'Created at:',
      'article{end_date}' => 'End date:',
      'article{book_id}' => 'Book:',
    );
  }
}
