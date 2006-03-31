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
    $chain           = array(),
    $index           = -1,
    $execution       = false,
    $executionFilter = null,
    $renderingFilter = null;

  /**
   * Execute the next filter in this chain.
   *
   * @return void
   */
  public function execute ()
  {
    ++$this->index;

    $max = count($this->chain);
    if ($this->index < $max)
    {
      $filter = $this->chain[$this->execution ? ($max - $this->index - 1) : $this->index];

      // method to execute?
      $method = '';
      if ($this->execution)
      {
        $method = method_exists($filter, 'executeBeforeRendering') ? 'executeBeforeRendering' : '';
      }
      else
      {
        $method = method_exists($filter, 'executeBeforeExecution') ? 'executeBeforeExecution' : (method_exists($filter, 'execute') ? 'execute' : '');
      }
    }
    else if ($this->index == $max)
    {
      $filter = $this->execution ? $this->renderingFilter : $this->executionFilter;
      $method = 'execute';
    }

    if (sfConfig::get('sf_logging_active'))
    {
      sfContext::getInstance()->getLogger()->info(sprintf('{sfFilterChain} executing filter "%s"', get_class($filter)));
    }

    if (!$method)
    {
      // execute next filter
      $this->execute();
    }
    else
    {
      // execute filter
      $filter->$method($this);
    }
  }

  public function executionFilterDone ()
  {
    $this->execution = true;
    $this->index     = -1;
  }

  /**
   * Register a filter with this chain.
   *
   * @param Filter A Filter implementation instance.
   *
   * @return void
   */
  public function register ($filter)
  {
    $this->chain[] = $filter;
  }

  public function registerExecution ($filter)
  {
    $this->executionFilter = $filter;
  }

  public function registerRendering ($filter)
  {
    $this->renderingFilter = $filter;
  }
}

?>