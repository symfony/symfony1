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
abstract class sfPHPView extends sfView
{
  private static
    $coreHelpersLoaded = 0;

  /**
   * Assigns some common variables to the template.
   */
  private function assignGlobalVars()
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
  private function assignModuleVars()
  {
    $action = $this->getContext()->getActionStack()->getLastEntry()->getActionInstance();
    $this->attribute_holder->add($action->getVarHolder()->getAll());
  }

  private function loadCoreAndStandardHelpers()
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
  private function loadHelpers($helpers)
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

  private function renderFile($file)
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
    $template = $this->getDirectory().'/'.$this->getTemplate();
    $actionInstance = $this->getContext()->getActionStack()->getLastEntry()->getActionInstance();

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

      // ignore cache parameter? (only available in debug mode)
      if (SF_CACHE && !count($_GET) && !count($_POST))
      {
        if (SF_DEBUG && $this->getContext()->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
        {
          if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfPHPView} discard cache for "'.$template.'"');
        }
        else
        {
          // retrieve content from cache
          $retval = $this->getContext()->getViewCacheManager()->get($moduleName, $actionName);

          if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfPHPView} cache '.($retval ? 'exists' : 'does not exist'));
        }
      }

      // render template if no cache
      if ($retval === null)
      {
        $retval = $this->renderFile($template);

        // tidy our cache content
        if (SF_TIDY)
        {
          $retval = sfTidy::tidy($retval, $template);
        }

        // save content in cache
        // no cache for POST and GET action
        if (SF_CACHE && !count($_GET) && !count($_POST))
        {
          $retval = $this->getContext()->getViewCacheManager()->set($retval, $moduleName, $actionName);
        }
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

        // save content in cache
        // no cache for POST action
        if (SF_CACHE && $this->getContext()->getRequest()->getMethod() != sfRequest::POST)
        {
          $retval = $this->getContext()->getViewCacheManager()->set($retval, $moduleName, $actionName, 'page');
        }

        echo $retval;
        $retval = null;
      }
    }

    return $retval;
  }
}

?>