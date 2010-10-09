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
 * @version    SVN: $Id: sfWebDebugFilter.class.php 16942 2009-04-03 14:48:17Z fabien $
 */
class sfWebDebugFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute this filter only once
    if ($this->isFirstCall())
    {
      // register sfWebDebug assets
      sfWebDebug::getInstance()->registerAssets();
    }

    // execute next filter
    $filterChain->execute();

    $context    = $this->getContext();
    $response   = $context->getResponse();
    $controller = $context->getController();

    // don't add debug toolbar:
    // * for XHR requests
    // * if response status code is in the 3xx range
    // * if not rendering to the client
    // * if HTTP headers only
    if (
      $this->getContext()->getRequest()->isXmlHttpRequest() ||
      strpos($response->getContentType(), 'html') === false ||
      '3' == substr($response->getStatusCode(), 0, 1) ||
      $controller->getRenderMode() != sfView::RENDER_CLIENT ||
      $response->isHeaderOnly()
    )
    {
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
}
