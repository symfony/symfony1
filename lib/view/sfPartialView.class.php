<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A View to render partials.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPartialView.class.php 13479 2008-11-29 13:52:41Z Kris.Wallsmith $
 */
class sfPartialView extends sfPHPView
{
  protected
    $partialVars = array();

  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * @param array $partialvars
   */
  public function setPartialVars(array $partialVars)
  {
    $this->partialVars = $partialVars;
    $this->getAttributeHolder()->add($partialVars);
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    $this->setDecorator(false);
    $this->setTemplate($this->actionName.$this->getExtension());
    if ('global' == $this->moduleName)
    {
      $this->setDirectory($this->context->getConfiguration()->getDecoratorDir($this->getTemplate()));
    }
    else
    {
      $this->setDirectory($this->context->getConfiguration()->getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Renders the presentation.
   *
   * @return string Current template content
   */
  public function render()
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    if ($retval = $this->getCache())
    {
      return $retval;
    }
    else if (sfConfig::get('sf_cache'))
    {
      $mainResponse = $this->context->getResponse();
      $responseClass = get_class($mainResponse);
      $this->context->setResponse($response = new $responseClass($this->context->getEventDispatcher(), array_merge($mainResponse->getOptions(), array('content_type' => $mainResponse->getContentType()))));
    }

    // execute pre-render check
    $this->preRenderCheck();

    $this->getAttributeHolder()->set('sf_type', 'partial');

    // render template
    $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

    if (sfConfig::get('sf_cache'))
    {
      $retval = $this->viewCache->setPartialCache($this->moduleName, $this->actionName, $this->cacheKey, $retval);
      $this->context->setResponse($mainResponse);
      $mainResponse->merge($response);
    }

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $retval;
  }

  public function getCache()
  {
    if (!sfConfig::get('sf_cache'))
    {
      return null;
    }

    $this->viewCache = $this->context->getViewCacheManager();
    $this->viewCache->registerConfiguration($this->moduleName);

    $this->cacheKey = $this->viewCache->computeCacheKey($this->partialVars);
    if ($retval = $this->viewCache->getPartialCache($this->moduleName, $this->actionName, $this->cacheKey))
    {
      return $retval;
    }
  }
}
