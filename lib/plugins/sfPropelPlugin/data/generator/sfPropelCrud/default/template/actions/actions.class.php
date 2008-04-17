[?php

/**
 * <?php echo $this->getModuleName() ?> actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName() ?>

 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id$
 */
class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function executeIndex()
  {
    $this-><?php echo $this->getSingularName() ?>List = <?php echo $this->getClassName() ?>Peer::doSelect(new Criteria());
  }

<?php if (isset($this->params['with_show']) && $this->params['with_show']): ?>
  public function executeShow($request)
  {
    $this-><?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForAction(49, '$request->getParameter') ?>);
    $this->forward404Unless($this-><?php echo $this->getSingularName() ?>);
  }

<?php endif; ?>
<?php if (isset($this->params['non_atomic_actions']) && $this->params['non_atomic_actions']): ?>
  public function executeEdit($request)
  {
    $this->form = new <?php echo $this->getClassName() ?>Form(<?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForEdit(49, $this->getSingularName()) ?>));

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('<?php echo $this->getSingularName() ?>'));
      if ($this->form->isValid())
      {
        $<?php echo $this->getSingularName() ?> = $this->form->save();

        $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>);
      }
    }
  }
<?php else: ?>
  public function executeCreate()
  {
    $this->form = new <?php echo $this->getClassName() ?>Form();

    $this->setTemplate('edit');
  }

  public function executeEdit($request)
  {
    $this->form = new <?php echo $this->getClassName() ?>Form(<?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForAction(49, '$request->getParameter') ?>));
  }

  public function executeUpdate($request)
  {
    $this->forward404Unless($request->isMethod('post'));

    $this->form = new <?php echo $this->getClassName() ?>Form(<?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForAction(49, '$request->getParameter') ?>));

    $this->form->bind($request->getParameter('<?php echo $this->getSingularName() ?>'));
    if ($this->form->isValid())
    {
      $<?php echo $this->getSingularName() ?> = $this->form->save();

      $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>);
    }

    $this->setTemplate('edit');
  }
<?php endif; ?>

  public function executeDelete($request)
  {
    $this->forward404Unless($<?php echo $this->getSingularName() ?> = <?php echo $this->getClassName() ?>Peer::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForAction(43, '$request->getParameter') ?>));

    $<?php echo $this->getSingularName() ?>->delete();

    $this->redirect('<?php echo $this->getModuleName() ?>/index');
  }
}
