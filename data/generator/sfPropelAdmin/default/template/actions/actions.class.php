[?php

/**
 * <?php echo $this->getGeneratedModuleName() ?> actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getGeneratedModuleName() ?>

 * @author     Your name here
 * @version    SVN: $Id$
 */
class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function preExecute ()
  {
    $this->getResponse()->addStylesheet('/sf/css/sf_admin/main', 'first');
  }

  public function executeIndex ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
  }

  public function executeList ()
  {
    $this->processSort();

    $this->processFilters();

    // pager
    $this->pager = new sfPropelPager('<?php echo $this->getClassName() ?>', <?php echo $this->getParameterValue('list.max_per_page', 20) ?>);
    $c = new Criteria();
    $this->addSortCriteria($c);
    $this->addFiltersCriteria($c);
    $this->pager->setCriteria($c);
    $this->pager->setPage($this->getRequestParameter('page', 1));
    $this->pager->init();
  }

  public function executeCreate ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'edit');
  }

  public function executeSave ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'edit');
  }

  public function executeEdit ()
  {
    // add javascript
    $this->getResponse()->addJavascript('/sf/js/prototype/prototype');
    $this->getResponse()->addJavascript('/sf/js/sf_admin/collapse');

    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $this->update<?php echo $this->getClassName() ?>FromRequest();
      $this-><?php echo $this->getSingularName() ?>->save();

      $this->setFlash('notice', 'Your modifications have been saved');

      return $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams('this->') ?>);
<?php //' ?>
    }
  }

  public function executeDelete ()
  {
    $this-><?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForDelete() ?>);
    $this->forward404Unless($this-><?php echo $this->getSingularName() ?>);

    $this-><?php echo $this->getSingularName() ?>->delete();

    return $this->redirect('<?php echo $this->getModuleName() ?>/list');
  }

  public function handleErrorEdit()
  {
    $this->preExecute();
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();
    $this->update<?php echo $this->getClassName() ?>FromRequest();

    return sfView::SUCCESS;
  }

  protected function update<?php echo $this->getClassName() ?>FromRequest()
  {
    $<?php echo $this->getSingularName() ?> = $this->getRequestParameter('<?php echo $this->getSingularName() ?>');

<?php foreach ($this->getColumnCategories('edit.display') as $category): ?>
<?php foreach ($this->getColumns('edit.display', $category) as $name => $column): $type = $column->getCreoleType(); ?>
<?php $name = $column->getName() ?>
<?php if ($column->isPrimaryKey()) continue ?>
<?php $credentials = $this->getParameterValue('edit.fields.'.$column->getName().'.credentials') ?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
    if ($this->getUser()->hasCredential(<?php echo $credentials ?>))
    {
<?php endif ?>
    if (isset($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']))
    {
<?php if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP): ?>
      if ($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'])
      {
        list($d, $m, $y) = sfI18N::getDateForCulture($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'], $this->getUser()->getCulture());
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>("$y-$m-$d");
      }
      else
      {
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>(null);
      }
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
      $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>(isset($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']) ? $<?php echo $this->getSingularName() ?>['<?php echo $name ?>'] : 0);
<?php else: ?>
      $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']);
<?php endif ?>
    }
<?php if ($credentials): ?>
      }
<?php endif ?>
<?php endforeach ?>
<?php endforeach ?>
  }

  protected function get<?php echo $this->getClassName() ?>OrCreate (<?php echo $this->getMethodParamsForGetOrCreate() ?>)
  {
    if (<?php echo $this->getTestPksForGetOrCreate() ?>)
    {
      $<?php echo $this->getSingularName() ?> = new <?php echo $this->getClassName() ?>();
    }
    else
    {
      $<?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForGetOrCreate() ?>);

      $this->forward404Unless($<?php echo $this->getSingularName() ?>);
    }

    return $<?php echo $this->getSingularName() ?>;
  }

  protected function processFilters ()
  {
<?php if ($this->getParameterValue('list.filters')): ?>
    if ($this->getRequest()->hasParameter('filter'))
    {
      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/<?php echo $this->getSingularName() ?>/filters');
      $this->getUser()->getAttributeHolder()->add($this->getRequestParameter('filters'), 'sf_admin/<?php echo $this->getSingularName() ?>/filters');
    }
<?php endif ?>
  }

  protected function processSort ()
  {
    if ($this->getRequestParameter('sort'))
    {
      $this->getUser()->setAttribute('sort', $this->getRequestParameter('sort'), 'sf_admin/<?php echo $this->getSingularName() ?>/sort');
      $this->getUser()->setAttribute('type', $this->getRequestParameter('type', 'asc'), 'sf_admin/<?php echo $this->getSingularName() ?>/sort');
    }
  }

  protected function addFiltersCriteria (&$c)
  {
<?php if ($this->getParameterValue('list.filters')): ?>
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/<?php echo $this->getSingularName() ?>/filters');
<?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
    if (isset($this->filters['<?php echo $column->getName() ?>']) && $this->filters['<?php echo $column->getName() ?>'] !== '')
    {
<?php if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR): ?>
      $c->add(<?php echo $this->getPeerClassName() ?>::<?php echo strtoupper($column->getName()) ?>, strtr($this->filters['<?php echo $column->getName() ?>'], '*', '%'), Criteria::LIKE);
<?php else: ?>
      $c->add(<?php echo $this->getPeerClassName() ?>::<?php echo strtoupper($column->getName()) ?>, $this->filters['<?php echo $column->getName() ?>']);
<?php endif ?>
    }
<?php endforeach ?>
<?php endif ?>
  }

  protected function addSortCriteria (&$c)
  {
    if ($sort_column = $this->getUser()->getAttribute('sort', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort'))
    {
      if ($this->getUser()->getAttribute('type', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == 'asc')
      {
        $c->addAscendingOrderByColumn($sort_column);
      }
      else
      {
        $c->addDescendingOrderByColumn($sort_column);
      }
    }
  }
}

?]