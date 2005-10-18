<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfExecutionFilter is the last filter registered for each filter chain. This
 * filter does all action and view execution.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfExecutionFilter extends sfFilter
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
    // get the context and controller
    $context    = $this->getContext();
    $controller = $context->getController();

    // create validator manager
    $validatorManager = new sfValidatorManager();
    $validatorManager->initialize($context);

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // get the current action information
    $moduleName = $context->getModuleName();
    $actionName = $context->getActionName();

    // get the request method
    $method = $context->getRequest()->getMethod();

    if (($actionInstance->getRequestMethods() & $method) != $method)
    {
      // this action will skip validation/execution for this method
      // get the default view
      $viewName = $actionInstance->getDefaultView();
    }
    else
    {
      // set default validated status
      $validated = true;

      // get the current action validation configuration
      $validationConfig = $moduleName.'/'.SF_APP_MODULE_VALIDATE_DIR_NAME.'/'.$actionName.'.yml';
      if (is_readable(SF_APP_MODULE_DIR.'/'.$validationConfig))
      {
        // load validation configuration
        // do NOT use require_once
        require(sfConfigCache::checkConfig(SF_APP_MODULE_DIR_NAME.'/'.$validationConfig));
      }

      // manually load validators
      $actionInstance->registerValidators($validatorManager);

      // process validators
      $validated = $validatorManager->execute();

      // process manual validation
      if ($validated && $actionInstance->validate())
      {
        // register our cache configuration
        if (SF_CACHE)
        {
          $cacheManager = $context->getViewCacheManager();
          $cacheConfigFile = $moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/cache.yml';
          if (is_readable(SF_APP_MODULE_DIR.'/'.$cacheConfigFile))
          {
            require(sfConfigCache::checkConfig(SF_APP_MODULE_DIR_NAME.'/'.$cacheConfigFile, array('moduleName' => $moduleName)));
          }

        }

        // page in cache?
        if (SF_CACHE && !count($_GET) && !count($_POST))
        {
          if (SF_DEBUG && $context->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
          {
            if (SF_LOGGING_ACTIVE) $context->getLogger()->info('{sfExecutionFilter} discard page cache');
          }
          else
          {
            // retrieve page content from cache
            $retval = $cacheManager->get($moduleName, $actionName, 'page');

            if (SF_LOGGING_ACTIVE) $context->getLogger()->info('{sfExecutionFilter} page cache '.($retval ? 'exists' : 'does not exist'));

            if ($retval !== null)
            {
              if ($controller->getRenderMode() == sfView::RENDER_VAR)
              {
                $actionEntry->setPresentation($retval);
              }
              else
              {
                // conditionnal get support
                // http://fishbowl.pastiche.org/archives/001132.html
                // http://simon.incutio.com/archive/2003/04/23/conditionalGet
                // http://lightpress.org/post/php-http11-dates-and-conditional-get/
                // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
// FIXME: TBD
//                if (!$this->doConditionalGet(time()))
//                {
                  echo $retval;
//                }
              }

              // stop execution filter
              return;
            }
          }
        }

        // execute the action
        $actionInstance->preExecute();
        $viewName = $actionInstance->execute();
        if ($viewName == '')
        {
          $viewName = sfView::SUCCESS;
        }
        $actionInstance->postExecute();
      }
      else
      {
        if (SF_LOGGING_ACTIVE) $this->context->getLogger()->info('{sfExecutionFilter} action validation failed');

        // validation failed
        $viewName = $actionInstance->handleError();
      }
    }

    if ($viewName != sfView::NONE)
    {
      if (is_array($viewName))
      {
        // we're going to use an entirely different action for this view
        $moduleName = $viewName[0];
        $viewName   = $viewName[1];
      }
      else
      {
        // use a view related to this action
        $viewName = $actionName.$viewName;
      }

      // display this view
      if (!$controller->viewExists($moduleName, $viewName))
      {
        // the requested view doesn't exist
        $file = SF_APP_MODULE_DIR.'/'.$moduleName.'/'.SF_APP_MODULE_VIEW_DIR_NAME.'/'.$viewName.'View.class.php';

        $error = 'Module "%s" does not contain the view "%sView" or the file "%s" is unreadable';
        $error = sprintf($error, $moduleName, $viewName, $file);

        throw new sfViewException($error);
      }

      // get the view instance
      $viewInstance = $controller->getView($moduleName, $viewName);

      // initialize the view
      if ($viewInstance->initialize($context, $moduleName, $viewName))
      {
        // view initialization completed successfully
        $viewInstance->execute();

        // render the view and if data is returned, stick it in the
        // action entry which was retrieved from the execution chain
        $viewData =& $viewInstance->render();

        if ($controller->getRenderMode() == sfView::RENDER_VAR)
        {
          $actionEntry->setPresentation($viewData);
        }
      }
      else
      {
        // view failed to initialize
        $error = 'View initialization failed for module "%s", view "%sView"';
        $error = sprintf($error, $moduleName, $viewName);

        throw new sfInitializationException($error);
      }
    }
  }

  private function doConditionalGet($timestamp)
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