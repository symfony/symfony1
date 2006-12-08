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
  protected
    $chain = array(),
    $index = -1;

  /**
   * Execute the next filter in this chain.
   *
   * @return void
   */
  public function execute()
  {
    // skip to the next filter
    ++$this->index;

    if ($this->index < count($this->chain))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info(sprintf('{sfFilter} executing filter "%s"', get_class($this->chain[$this->index])));
      }

      // execute the next filter
      $this->chain[$this->index]->execute($this);
    }
  }

  public function hasFilter($class)
  {
    foreach ($this->chain as $filter)
    {
      if ($filter instanceof $class)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Register a filter with this chain.
   *
   * @param Filter A Filter implementation instance.
   *
   * @return void
   */
  public function register($filter)
  {
    $this->chain[] = $filter;
  }
}
