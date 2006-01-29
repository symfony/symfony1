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
class sfCommonFilter extends sfFilter
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
      $context  = $this->getContext();
      $request  = $context->getRequest();
      $response = $context->getResponse();

      // remove PHP automatic Cache-Control and Expires headers if not overwritten by application
      $response->setHttpHeader('Cache-Control', null, false);
      $response->setHttpHeader('Expires', null, false);

      // Etag support
      if ($this->getParameter('etag', true))
      {
        $etag = md5($response->getContent());

        if ($request->getHttpHeader('IF_NONE_MATCH') == $etag)
        {
          $response->setStatusCode(304);
          $response->setContent('');

          if (sfConfig::get('sf_logging_active'))
          {
            $this->getContext()->getLogger()->info('{sfCommonFilter} ETag matches If-None-Match (send 304)');
          }
        }
        else
        {
          $response->setHttpHeader('ETag', $etag);
        }
      }

      // conditional GET support
      if ($response->hasHeader('Last-Modified'))
      {
        if ($request->getHttpHeader('IF_MODIFIED_SINCE') == $response->getHeader('Last-Modified'))
        {
          $response->setStatusCode(304);
          $response->setContent('');

          if (sfConfig::get('sf_logging_active'))
          {
            $this->getContext()->getLogger()->info('{sfCommonFilter} Last-Modified matches If-Modified-Since (send 304)');
          }
        }
      }
    }

    // execute next filter
    $filterChain->execute();
  }
}

?>