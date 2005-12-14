<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfPHPView.class.php 533 2005-10-18 12:44:42Z fabien $
 */
class sfPHPView extends sfView
{
  protected static
    $coreHelpersLoaded = 0;

  public function execute()
  {
  }

  /**
   * Assigns some common variables to the template.
   */
  protected function assignGlobalVars()
  {
    $context = $this->getContext();

    $lastActionEntry = $context->getActionStack()->getLastEntry();
    $firstActionEntry = $context->getActionStack()->getFirstEntry();

    $shortcuts = array(
      'context'       => $context,
      'params'        => $context->getRequest()->getParameterHolder(),
      'request'       => $context->getRequest(),
      'user'          => $context->getUser(),
      'view'          => $this,
      'last_module'   => $lastActionEntry->getModuleName(),
      'last_action'   => $lastActionEntry->getActionName(),
      'first_module'  => $firstActionEntry->getModuleName(),
      'first_action'  => $firstActionEntry->getActionName(),
    );

    $this->attribute_holder->add($shortcuts);
  }

  /**
   * Assigns some action variables to the template.
   */
  protected function assignModuleVars()
  {
    $action = $this->getContext()->getActionStack()->getLastEntry()->getActionInstance();
    $this->attribute_holder->add($action->getVarHolder()->getAll());
  }

  protected function loadCoreAndStandardHelpers()
  {
    if (self::$coreHelpersLoaded)
    {
      return;
    }

    self::$coreHelpersLoaded = 1;

    $core_helpers = array('Helper', 'Url', 'Asset', 'Tag');
    $standard_helpers = explode(',', SF_STANDARD_HELPERS);

    $helpers = array_unique(array_merge($core_helpers, $standard_helpers));
    $this->loadHelpers($helpers);
  }

  /**
   * Loads all template helpers.
   *
   * helpers defined in templates (set with use_helper())
   */
  protected function loadHelpers($helpers)
  {
    foreach ($helpers as $helperName)
    {
      if (is_readable(SF_SYMFONY_LIB_DIR.'/symfony/helper/'.$helperName.'Helper.php'))
      {
        include_once('symfony/helper/'.$helperName.'Helper.php');
      }
      else
      {
        include_once('helper/'.$helperName.'Helper.php');
      }
    }
  }

  protected function renderFile($file)
  {
    $this->assignGlobalVars();
    $this->assignModuleVars();

    extract($this->attribute_holder->getAll());

    $this->loadCoreAndStandardHelpers();

    // render to variable
    ob_start();
    ob_implicit_flush(0);
    require($file);
    $retval = ob_get_clean();

    return $retval;
  }

  /**
   * Retrieve the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function &getEngine()
  {
    return null;
  }

  public function configure($extension = '.php')
  {
    $context          = $this->getContext();
    $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
    $action           = $actionStackEntry->getActionInstance();

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/view.yml';
    require(sfConfigCache::checkConfig(SF_APP_MODULE_DIR_NAME.'/'.$viewConfigFile));

    $viewType = sfView::SUCCESS;
    if (preg_match('/^'.$action->getActionName().'(.+)$/i', $this->viewName, $match))
    {
      $viewType = $match[1];
      $templateFile = $templateName.$viewType.$extension;
    }
    else
    {
      $templateFile = $this->viewName.$extension;
    }

    // set template name
    $this->setTemplate($templateFile);

    // set template directory
    $module = $context->getModuleName();
    if (!is_readable($this->getDirectory().'/'.$templateFile))
    {
      // search template in a symfony module directory
      if (is_readable(SF_SYMFONY_DATA_DIR.'/symfony/modules/'.$module.'/templates/'.$templateFile))
      {
        $this->setDirectory(SF_SYMFONY_DATA_DIR.'/symfony/modules/'.$module.'/templates');
      }

      // search template for generated templates in cache
      if (is_readable(SF_MODULE_CACHE_DIR.'/auto'.ucfirst($module).'/templates/'.$templateFile))
      {
        $this->setDirectory(SF_MODULE_CACHE_DIR.'/auto'.ucfirst($module).'/templates');
      }
    }

    if (SF_LOGGING_ACTIVE) $context->getLogger()->info('{sfPHPView} execute view for template "'.$templateName.$viewType.$extension.'"');
  }

  /**
   * Loop through all template slots and fill them in with the results of
   * presentation data.
   *
   * @param string A chunk of decorator content.
   *
   * @return string A decorated template.
   */
  protected function &decorate(&$content)
  {
    $template = $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate();

    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfPHPView} decorate content with "'.$template.'"');

    // call our parent decorate() method
    parent::decorate($content);

    // render the decorator template and return the result 
    $retval = $this->renderFile($template);

    return $retval;
  }

  /**
   * Render the presentation.
   *
   * When the controller render mode is sfView::RENDER_CLIENT, this method will
   * render the presentation directly to the client and null will be returned.
   *
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise null.
   */
  public function &render()
  {
    $template         = $this->getDirectory().'/'.$this->getTemplate();
    $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
    $actionInstance   = $actionStackEntry->getActionInstance();

    $moduleName = $actionInstance->getModuleName();
    $actionName = $actionInstance->getActionName();

    $retval = null;

    // execute pre-render check
    $this->preRenderCheck();

    // get the render mode
    $mode = $this->getContext()->getController()->getRenderMode();

    if ($mode != sfView::RENDER_NONE)
    {
      if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfPHPView} render "'.$template.'"');

      $retval = $this->getCacheContent();

      // render template if no cache
      if ($retval === null)
      {
        $retval = $this->renderFile($template);

        // tidy our cache content
        if (SF_TIDY)
        {
          $retval = sfTidy::tidy($retval, $template);
        }

        $retval = $this->setCacheContent($retval);
      }

      // now render decorator template, if one exists
      if ($this->isDecorator())
      {
        $retval =& $this->decorate($retval);
      }

      // render to client
      if ($mode == sfView::RENDER_CLIENT)
      {
        if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfPHPView} render to client');

        $retval = $this->setPageCacheContent($retval);

        // send custom headers
        foreach ($this->getContext()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/httpmeta') as $httpequiv => $value)
        {
          header($httpequiv.': '.$value);
        }

        echo $retval;
        $retval = null;
      }
    }

    return $retval;
  }

  protected function getInternalUri()
  {
    $actionStackEntry = $this->getContext()->getController()->getActionStack()->getLastEntry();
    $internalUri = sfRouting::getInstance()->getCurrentInternalUri();
    $suffix      = 'slot';

    if ($actionStackEntry->isSlot())
    {
      $suffix = preg_replace('/[^a-z0-9]/i', '_', $internalUri);
      $suffix = preg_replace('/_+/', '_', $suffix);

      $actionInstance = $actionStackEntry->getActionInstance();
      $moduleName     = $actionInstance->getModuleName();
      $actionName     = $actionInstance->getActionName();
      $internalUri    = $moduleName.'/'.$actionName;

      // we add cache information based on slot configuration for this module/action
      $cacheManager = $this->getContext()->getViewCacheManager();
      $lifeTime     = $cacheManager->getLifeTime($internalUri, 'slot');
      if ($lifeTime)
      {
        $cacheManager->addCache($moduleName, $actionName, $suffix, $lifeTime);
      }
    }

    return array($internalUri, $suffix);
  }

  protected function setCacheContent($retval)
  {
    // save content in cache
    // no cache for POST and GET action
    if (SF_CACHE && !count($_GET) && !count($_POST))
    {
      list($internalUri, $suffix) = $this->getInternalUri();
      $retval = $this->getContext()->getViewCacheManager()->set($retval, $internalUri, $suffix);
    }

    return $retval;
  }

  protected function setPageCacheContent($retval)
  {
    // save content in cache
    // no cache for POST action
    if (SF_CACHE && $this->getContext()->getRequest()->getMethod() != sfRequest::POST)
    {
      $retval = $this->getContext()->getViewCacheManager()->set($retval, sfRouting::getInstance()->getCurrentInternalUri(), 'page');
    }

    return $retval;
  }

  protected function getCacheContent()
  {
    $retval = null;

    // ignore cache parameter? (only available in debug mode)
    if (SF_CACHE && !count($_GET) && !count($_POST))
    {
      if (SF_DEBUG && $this->getContext()->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
      {
        if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfView} discard cache for "'.$template.'"');
      }
      else
      {
        // retrieve content from cache
        list($internalUri, $suffix) = $this->getInternalUri();
        $retval = $this->getContext()->getViewCacheManager()->get($internalUri, $suffix);

        if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfView} cache '.($retval !== null ? 'exists' : 'does not exist'));
      }
    }

    return $retval;
  }
}

?>