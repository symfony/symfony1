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
    // add our css file automatically
    $this->getRequest()->setAttribute('admin_generator_main',
      array('/sf/css/sf_admin/main'),
      'helper/asset/auto/stylesheet'
    );
  }

  public function executeIndex ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
  }

  public function executeList ()
  {
    $this-><?php echo $this->getPluralName() ?> = <?php echo $this->getClassName() ?>Peer::doSelect(new Criteria());
  }

  public function executeEdit ()
  {
    // add our js file automatically
    $this->getRequest()->setAttribute('admin_generator_main',
      array('/sf/js/prototype', '/sf/js/sf_admin/collapse'),
      'helper/asset/auto/javascript'
    );

    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $this->update<?php echo $this->getClassName() ?>FromRequest();
      $this-><?php echo $this->getSingularName() ?>->save();

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

  private function update<?php echo $this->getClassName() ?>FromRequest()
  {
<?php foreach ($this->getColumnCategories('edit_fields') as $category): ?>
<?php foreach ($this->getColumns('edit_fields', $category) as $name => $column): $type = $column->getCreoleType(); ?>
<?php $name = $column->getName() ?>
<?php if ($type == CreoleTypes::DATE): ?>
    list($d, $m, $y) = sfI18N::getDateForCulture($this->getRequestParameter('<?php echo $name ?>'), $this->getUser()->getCulture());
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>("$y-$m-$d");
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $name ?>', 0));
<?php else: ?>
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $name ?>'));
<?php endif ?>
<?php endforeach ?>
<?php endforeach ?>
  }

  private function get<?php echo $this->getClassName() ?>OrCreate (<?php echo $this->getMethodParamsForGetOrCreate() ?>)
  {
    if (<?php echo $this->getTestPksForGetOrCreate() ?>)
    {
      $<?php echo $this->getSingularName() ?> = new <?php echo $this->getClassName() ?>();
    }
    else
    {
      $<?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForGetOrCreate() ?>);

      $this->forward404Unless($<?php echo $this->getSingularName() ?> instanceof <?php echo $this->getClassName() ?>);
    }

    return $<?php echo $this->getSingularName() ?>;
  }
}

?]