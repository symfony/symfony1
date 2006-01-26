<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRenderingFilter is the last filter registered for each filter chain. This
 * filter does the rendering.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfRenderingFilter extends sfFilter
{
  /**
   * Execute this filter.
   *
   * @param sfFilterChain The filter chain.
   *
   * @return void
   *
   * @throws <b>sfInitializeException</b> If an error occurs during view initialization.
   * @throws <b>sfViewException</b>       If an error occurs while executing the view.
   */
  public function execute ($filterChain)
  {
    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info('{sfRenderingFilter} render to client');
    }

    // get response object
    $response = $this->getContext()->getResponse();

    // send headers
    $response->sendHeaders();

    // send content
    $response->sendContent();
  }
}

?>