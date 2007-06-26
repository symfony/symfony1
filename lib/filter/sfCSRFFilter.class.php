<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCSRFFilter protects your forms against Cross Site Request Forgeries (http://en.wikipedia.org/wiki/Csrf).
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCSRFFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    if (!$secret = $this->getParameter('secret'))
    {
      throw new sfConfigurationException('You must provide a "secret" option for the sfCSRFPlugin filter.');
    }

    $request = $this->getContext()->getRequest();

    // check only if request method is POST
    if (sfRequest::POST === $request->getMethod())
    {
      $requestToken = $request->getParameter('_csrf_token');

      // error if no token or if token is not valid
      if (!$requestToken || md5($secret.session_id()) != $requestToken)
      {
        throw new sfException('CSRF attack detected.');
      }
    }

    // execute next filter
    $filterChain->execute();

    // nothing to do if content is not HTML
    $response = $this->getContext()->getResponse();
    $contentType = $response->getContentType();
    if (false === strpos($contentType, 'html') && $contentType)
    {
      return;
    }

    // add a token to every form in the response content
    $response->setContent(preg_replace('#(<form\b[^>]*\bmethod=(\'|")post\2[^>]*>)#i', '$1<input type="hidden" name="_csrf_token" value="'.md5($secret.session_id()).'" />', $response->getContent()));
  }
}
