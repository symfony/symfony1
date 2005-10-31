<?php echo '<'.'?'.'php'; ?>

// +---------------------------------------------------------------------------+
// | This file is part of the <?php echo $project->getName() ?> project.<?php echo str_repeat(' ', 40 - strlen($project->getName())) ?>|
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   <?php echo $project->getName() ?>.<?php echo $module->getName() ?> 
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id$
 */

class <?php echo $generatedModuleName ?>Actions extends sfActions
{
  public function execute()
  {
    $user = $this->getContext()->getUser();
    $controller = $this->getContext()->getController();

    $pager = $user->getPager('<?php echo $module->getClassName() ?>'<?php echo $module->getAttribute('paginateDefault') ? ', '.$module->getAttribute('paginateDefault') : '' ?>);
    $req->setAttribute('pager', $pager);

    <?php
      foreach ($action->getBlocks() as $block)
      {
        echo sfClassGeneration::getCode($block->getType(), 'Action', $module, $action);
      }
    ?>

    $pager->init();

    if ($this->getRequestParameter('tab', 'main') != 'main')
    {
      return ucfirst($this->getRequestParameter('tab')).'Success';
    }
    else
    {
      return sfView::SUCCESS;
    }

    public function isSecure ()
    {
      return true;
    }

    public function getCredential ()
    {
      return array('admin', '<?php echo $module->getName() ?>.<?php echo strtolower($action->getType()) ?>');
    }
  }
}

<?php echo '?'.'>' ?>