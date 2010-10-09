<?php

/**
 * inheritance actions.
 *
 * @package    project
 * @subpackage inheritance
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 2909 2006-12-04 15:15:42Z fabien $
 */
class inheritanceActions extends autoinheritanceActions
{
  protected function addFiltersCriteria($c)
  {
    if ($this->getRequestParameter('filter'))
    {
      $c->add(ArticlePeer::ONLINE, true);
    }
  }

  protected function addSortCriteria($c)
  {
    if ($this->getRequestParameter('sort'))
    {
      $c->addAscendingOrderByColumn(ArticlePeer::TITLE);
    }
  }
}
