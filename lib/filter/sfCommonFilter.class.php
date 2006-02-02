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

      // remove PHP automatic Cache-Control and Expires headers if not overwritten by application or cache
      if ($response->hasHeader('Last-Modified') || sfConfig::get('sf_etag'))
      {
        $response->setHttpHeader('Cache-Control', null, false);
        $response->setHttpHeader('Expires', null, false);
        $response->setHttpHeader('Pragma', null, false);
      }

      // Etag support
      if (sfConfig::get('sf_etag'))
      {
        $etag = md5($response->getContent());
        $response->setHttpHeader('ETag', $etag);

        if ($request->getHttpHeader('IF_NONE_MATCH') == $etag)
        {
          $response->setStatusCode(304);
          $response->setContent('');

          if (sfConfig::get('sf_logging_active'))
          {
            $this->getContext()->getLogger()->info('{sfCommonFilter} ETag matches If-None-Match (send 304)');
          }
        }
      }

      // conditional GET support
      if ($response->hasHeader('Last-Modified'))
      {
        $last_modified = $response->getHttpHeader('Last-Modified');
        $last_modified = $last_modified[0];
        if ($request->getHttpHeader('IF_MODIFIED_SINCE') == $last_modified)
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