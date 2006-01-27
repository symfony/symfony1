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
class sfCacheFilter extends sfFilter
{
  private
    $cacheManager = null,
    $toSave       = array();

  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->cacheManager = $context->getViewCacheManager();
  }

  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute ($filterChain)
  {
    if (sfConfig::get('sf_cache'))
    {
      // no cache if GET or POST parameters
      if (count($_GET) || count($_POST))
      {
        $filterChain->execute();

        return;
      }

      $context = $this->getContext();

      // register our cache configuration
      $cacheConfigFile = $context->getModuleName().'/'.sfConfig::get('sf_app_module_config_dir_name').'/cache.yml';
      if (is_readable(sfConfig::get('sf_app_module_dir').'/'.$cacheConfigFile))
      {
        require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$cacheConfigFile, array('moduleName' => $context->getModuleName())));
      }

      // page cache
      list($uri, $suffix) = $this->cacheManager->getInternalUri('page');
      $this->toSave[$uri.'_'.$suffix] = false;
      if ($this->cacheManager->hasCacheConfig($uri, $suffix))
      {
        $inCache = $this->getPageCache($uri, $suffix);
        $this->toSave[$uri.'_'.$suffix] = !$inCache;

        if ($inCache)
        {
          // don't run execution filter
          $filterChain->executionFilterDone();
        }
      }
      else
      {
        list($uri, $suffix) = $this->cacheManager->getInternalUri('slot');
        $this->toSave[$uri.'_'.$suffix] = false;
        if ($this->cacheManager->hasCacheConfig($uri, $suffix))
        {
          $inCache = $this->getActionCache($uri, $suffix);
          $this->toSave[$uri.'_'.$suffix] = !$inCache;
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
    if (sfConfig::get('sf_cache'))
    {
      // no cache if GET or POST parameters
      if (count($_GET) || count($_POST))
      {
        $filterChain->execute();

        return;
      }

      // save page in cache
      list($uri, $suffix) = $this->cacheManager->getInternalUri('page');
      if ($this->toSave[$uri.'_'.$suffix])
      {
        $this->setPageCache($uri, $suffix);
      }

      // save slot in cache
      list($uri, $suffix) = $this->cacheManager->getInternalUri('slot');
      if (isset($this->toSave[$uri.'_'.$suffix]) && $this->toSave[$uri.'_'.$suffix])
      {
        $this->setActionCache($uri, $suffix);
      }
    }

    // execute next filter
    $filterChain->execute();
  }

  private function setPageCache($uri, $suffix)
  {
    $context = $this->getContext();

    if ($context->getController()->getRenderMode() != sfView::RENDER_CLIENT)
    {
      return;
    }

    $response = $context->getResponse();

    // save content in cache
    $content = $this->cacheManager->set($response->getContent(), $uri, $suffix);

    $response->setContent($content);

    if (sfConfig::get('sf_logging_active'))
    {
      $context->getLogger()->info('{sfCacheFilter} save page "'.$uri.' - '.$suffix.'" in cache');
    }
  }

  private function getPageCache($uri, $suffix)
  {
    $context = $this->getContext();

    // ignore cache?
    if (sfConfig::get('sf_debug') && $context->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
    {
      if (sfConfig::get('sf_logging_active'))
      {
        $context->getLogger()->info('{sfCacheFilter} discard page cache for "'.$uri.' - '.$suffix.'"');
      }

      return false;
    }

    // get the current action information
    $moduleName = $context->getModuleName();
    $actionName = $context->getActionName();

    $retval = $this->cacheManager->get($uri, $suffix);

    if (sfConfig::get('sf_logging_active'))
    {
      $context->getLogger()->info('{sfCacheFilter} page cache "'.$uri.' - '.$suffix.'" '.($retval ? 'exists' : 'does not exist'));
    }

    if ($retval !== null)
    {
      $controller = $context->getController();
      if ($controller->getRenderMode() == sfView::RENDER_VAR)
      {
        $controller->getActionStack()->getLastEntry()->setPresentation($retval);
        $context->getResponse()->setContent('');
      }
      else
      {
        //if (!$this->doConditionalGet(time()))
        //{
        $context->getResponse()->setContent($retval);
        //}
      }

      return true;
    }

    return false;
  }

  private function setActionCache($uri, $suffix)
  {
    $content = $this->getContext()->getResponse()->getParameter($uri.'_'.$suffix, null, 'symfony/cache');
    $this->cacheManager->set($content, $uri, $suffix);

    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info('{sfCacheFilter} save slot "'.$uri.' - '.$suffix.'" in cache');
    }
  }

  private function getActionCache($uri, $suffix)
  {
    // ignore cache parameter? (only available in debug mode)
    if (sfConfig::get('sf_debug') && $this->getContext()->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
    {
      if (sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfCacheFilter} discard cache for "'.$uri.'" / '.$suffix.'');
      }
    }
    else
    {
      // retrieve content from cache
      $retval = $this->cacheManager->get($uri, $suffix);

      if ($retval)
      {
        $this->getContext()->getResponse()->setParameter($uri.'_'.$suffix, $retval, 'symfony/cache');
      }

      if (sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfCacheFilter} cache for "'.$uri.' - '.$suffix.'" '.($retval !== null ? 'exists' : 'does not exist'));
      }

      return ($retval ? true : false);
    }

    return false;
  }

  // conditionnal get support
  // http://fishbowl.pastiche.org/archives/001132.html
  // http://simon.incutio.com/archive/2003/04/23/conditionalGet
  // http://lightpress.org/post/php-http11-dates-and-conditional-get/
  // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
  private function doConditionalGet ($timestamp)
  {
    // ETag is any quoted string
    $etag = '"'.$timestamp.'"';

    // RFC1123 date
    $rfc1123 = substr(gmdate('r', $timestamp), 0, -5).'GMT';

    // RFC1036 date
    $rfc1036 = gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';

    // asctime
    $ctime = gmdate('D M j H:i:s', $timestamp);

    // Send the headers
    header("Last-Modified: $rfc1123");
    header("ETag: $etag");

    // See if the client has provided the required headers
    $if_modified_since = $if_none_match = false;

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
      $if_modified_since = stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    if(isset($_SERVER['HTTP_IF_NONE_MATCH']))
    {
      $if_none_match = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
    }

    if (!$if_modified_since && !$if_none_match)
    {
      // both are missing
      return false;
    }

    // At least one of the headers is there - check them
    // check etag if it's there and there's no if-modified-since
    if ($if_none_match)
    {
      if ($if_none_match != $etag)
      {
        // etag is there but doesn't match
        return false;
      }
      if (!$if_modified_since && ($if_none_match == $etag))
      {
        header('HTTP/1.0 304 Not Modified');
        return true;
      }
    }

    if ($if_modified_since)
    {
      // check if-modified-since
      foreach (array($rfc1123, $rfc1036, $ctime) as $d)
      {
        if ($d == $if_modified_since)
        {
          // Nothing has changed since their last request - serve a 304
          header('HTTP/1.0 304 Not Modified');
          return true;
        }
      }
    }

    return false;
  }
}

?>