<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 *
 * sfBreadCrumb class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfBreadCrumb
{
  private $branch_nodes = array();
  private $slip_nodes = array();
  private $context = null;

  /**
   * Constructs a new BreadCrumb
   *
   * @param  object context object
   */
  public function __construct($context)
  {
    $this->context = $context;
  }

  /**
   * Returns a string description for the current breadcrumb.
   *
   * Useful for debugging purpose
   *
   * @return string
   */
  public function toString()
  {
    $actionEntry = $this->context->getController()->getActionStack()->getLastEntry();
    $current_module = $actionEntry->getModuleName();
    $current_action = $actionEntry->getActionName();

    $node = $this->findCurrentNode();

    $str = $current_module.'/'.$current_action;

    return $str;
  }

  /**
   * Finds the node which matches the current context.
   *
   * @return object node
   */
  public function findCurrentNode()
  {
    foreach ($this->branch_nodes as $node)
    {
        
    }
  }

  /**
   * Adds a new branch node.
   *
   * @param  object sfBreadCrumbNode object
   * @return object
   */
  public function addBranchNode($node)
  {
    $branch_nodes[] = $node;
  }

  /**
   * Adds a new slip node.
   *
   * @param  object sfBreadCrumbNode object
   * @return object
   */
  public function addSlipNode($node)
  {
    $slip_nodes[] = $node;
  }
}

/**
 *
 * BreadCrumbNode class.
 *
 * @package    SymFony.runtime.core
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @license    SymFony License 1.0
 * @version    SVN: $Id$
 */
class sfBreadCrumbNode
{
  private $name = '';
  private $actions = array();
  private $children = array();

  /**
   * Constructs a new node.
   *
   * @param  string node name
   * @param  array  list of actions
   */
  public function __construct($name, $actions)
  {
    $this->name = $name;
    $this->actions = $actions;
  }

  /**
   * Adds a new child node.
   *
   * @param  object sfBreadCrumbNode object
   */
  public function addChildNode($node)
  {
    $this->childrens[] = $node;
  }

  /**
   * Gets the name of this child.
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}

?>