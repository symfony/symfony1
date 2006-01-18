<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilterChain manages registered filters for a specific context.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfFilterChain
{
  private
    $chain = array(),
    $index = -1;

  /**
   * Execute the next filter in this chain.
   *
   * @return void
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public function execute ()
  {
    // skip to the next filter
    $this->index++;

    if ($this->index < count($this->chain))
    {
      // execute the next filter
      $this->chain[$this->index]->execute($this);
    }
  }

  /**
   * Register a filter with this chain.
   *
   * @param Filter A Filter implementation instance.
   *
   * @return void
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public function register ($filter)
  {
    $this->chain[] = $filter;
  }
}

?>