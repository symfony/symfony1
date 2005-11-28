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
  public function executeIndex ()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
  }

  public function executeList ()
  {
    $this-><?php echo $this->getPluralName() ?> = <?php echo $this->getClassName() ?>Peer::doSelect(new Criteria());
  }

  public function executeShow ()
  {
    $this-><?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForShow() ?>);

    $this->forward404Unless($this-><?php echo $this->getSingularName() ?> instanceof <?php echo $this->getClassName() ?>);
  }

  public function executeEdit ()
  {
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();
  }

  public function executeUpdate ()
  {
    $<?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

<?php foreach ($this->getTableMap()->getColumns() as $name => $column): $type = $column->getCreoleType(); ?>
<?php if ($name == 'CREATED_AT' || $name == 'UPDATED_AT') continue ?>
<?php if ($type == CreoleTypes::DATE): ?>
    list($d, $m, $y) = sfI18N::getDateForCulture($this->getRequestParameter('<?php echo $this->translateFieldName($column->getPhpName()) ?>'), $this->getUser()->getCulture());
    $<?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>("$y-$m-$d");
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
    $<?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $this->translateFieldName($column->getPhpName()) ?>', 0));
<?php else: ?>
    $<?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($this->getRequestParameter('<?php echo $this->translateFieldName($column->getPhpName()) ?>'));
<?php endif ?>
<?php endforeach ?>

    $<?php echo $this->getSingularName() ?>->save();

    return $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>);
<?php //' ?>
  }

  public function executeDelete ()
  {
    $<?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForDelete() ?>);

    $this->forward404Unless($<?php echo $this->getSingularName() ?> instanceof <?php echo $this->getClassName() ?>);

    $<?php echo $this->getSingularName() ?>->delete();

    return $this->redirect('<?php echo $this->getModuleName() ?>/list');
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