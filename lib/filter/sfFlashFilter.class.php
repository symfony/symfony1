<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFlashFilter extends sfFilter
{
  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute ($filterChain)
  {
    // execute this filter only once
    if ($this->isFirstCall())
    {
      // flag current flash to be removed after the execution filter
      $context = $this->getContext();
      $userAttributeHolder = $context->getUser()->getAttributeHolder();
      $names = $userAttributeHolder->getNames('symfony/flash');
      if ($names)
      {
        if (sfConfig::get('sf_logging_active'))
        {
          $context->getLogger()->info('{sfController} flag old flash messages ("'.implode('", "', $names).'")');
        }
        foreach ($names as $name)
        {
          $userAttributeHolder->set($name, true, 'symfony/flash/remove');
        }
      }
    }

    // execute next filter
    $filterChain->execute();
  }

  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function executeBeforeRendering ($filterChain)
  {
    // execute this filter only once
    if ($this->isFirstCallBeforeRendering())
    {
      // remove flash that are tagged to be removed
      $context = $this->getContext();
      $userAttributeHolder = $context->getUser()->getAttributeHolder();
      $names = $userAttributeHolder->getNames('symfony/flash/remove');
      if ($names)
      {
        if (sfConfig::get('sf_logging_active'))
        {
          $context->getLogger()->info('{sfController} remove old flash messages ("'.implode('", "', $names).'")');
        }
        foreach ($names as $name)
        {
          $userAttributeHolder->remove($name, 'symfony/flash');
          $userAttributeHolder->remove($name, 'symfony/flash/remove');
        }
      }
    }

    // execute next filter
    $filterChain->execute();
  }
}
