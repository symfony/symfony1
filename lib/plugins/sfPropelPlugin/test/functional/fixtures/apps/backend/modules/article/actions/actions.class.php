<?php

/**
 * article actions.
 *
 * @package    project
 * @subpackage article
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 5102 2007-09-15 07:47:25Z fabien $
 */
class articleActions extends autoarticleActions
{
  public function executeMyAction()
  {
    return $this->renderText('Selected '.implode(', ', $this->getRequestParameter('sf_admin_batch_selection', array())));
  }
}
