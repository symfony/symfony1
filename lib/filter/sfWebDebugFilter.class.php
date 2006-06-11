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
class sfWebDebugFilter extends sfFilter
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
    if ($this->isFirstCall() && sfConfig::get('sf_web_debug'))
    {
      // register sfWebDebug assets
      sfWebDebug::getInstance()->registerAssets();
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
    if ($this->isFirstCallBeforeRendering() && sfConfig::get('sf_web_debug'))
    {
      $response = $this->getContext()->getResponse();

      // don't add debug toolbar on XHR requests
      // don't add debug if 304
      if (
          $this->getContext()->getRequest()->isXmlHttpRequest() || 
          strpos($response->getContentType(), 'text/html') === false ||
          $response->getStatusCode() == 304
      )
      {
        $filterChain->execute();
        return;
      }

      $content  = $response->getContent();
      $webDebug = sfWebDebug::getInstance()->getResults();

      // add web debug information to response content
      $newContent = str_ireplace('</body>', $webDebug.'</body>', $content);
      if ($content == $newContent)
      {
        $newContent .= $webDebug;
      }

      $response->setContent($newContent);
    }

    // execute next filter
    $filterChain->execute();
  }
}
