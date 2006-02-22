<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAction executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfAction extends sfComponent
{
  private
    $security = array(),
    $template = '';

  /**
   * Gets current module name
   *
   * @return string
   */
  public function getModuleName()
  {
    return $this->getContext()->getModuleName();
  }

  /**
   * Gets current action name
   *
   * @return string
   */
  public function getActionName()
  {
    return $this->getContext()->getActionName();
  }

  /**
   * Initialize this action.
   *
   * @param sfContext The current application context.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize($context)
  {
    parent::initialize($context);

    // include security configuration
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$this->getModuleName().'/'.sfConfig::get('sf_app_module_config_dir_name').'/security.yml', true));

    return true;
  }

  public function preExecute ()
  {
  }

  public function postExecute ()
  {
  }

  /**
   * Returns true if current action template will be executed by the view.
   *
   * This is the case if:
   * - cache is off;
   * - action is not available;
   * - cache is not fresh enough.
   *
   * Use this method to know if you have to populate parameters for the template.
   *
   * @return boolean
   */
  // FIXME: does not work for fragment because config is created in template, too late...
  public function mustExecute($suffix = 'slot')
  {
    if (!sfConfig::get('sf_cache'))
    {
      return 1;
    }

    // ignore cache? (only in debug mode)
    if (sfConfig::get('sf_debug') && $this->request->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
    {
      return 1;
    }

    $cache = $this->getContext()->getViewCacheManager();
    $moduleName = $this->getModuleName();
    $actionName = $this->getActionName();

    return (!$cache->has($moduleName, $actionName, $suffix));
  }

  /**
   * Forwards current action to the default 404 error action
   *
   */
  public function forward404 ()
  {
    throw new sfError404Exception();
  }

  public function forward404Unless ($condition)
  {
    if (!$condition)
    {
      throw new sfError404Exception();
    }
  }

  public function forward404If ($condition)
  {
    if ($condition)
    {
      throw new sfError404Exception();
    }
  }

  /**
   * Redirects current action to the default 404 error action (with browser redirection)
   *
   */
  public function redirect404 ()
  {
    return $this->redirect('/'.sfConfig::get('sf_error_404_module').'/'.sfConfig::get('sf_error_404_action'));
  }

  /**
   * Forwards current action to a new one (without browser redirection).
   *
   * This method must be called as with a return:
   *
   * <code>return $this->forward('module', 'action')</code>
   *
   * @param  string module name
   * @param  string action name
   * @return sfView::NONE
   */
  public function forward ($module, $action)
  {
    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfAction} forward to action "'.$module.'/'.$action.'"');

    $this->getController()->forward($module, $action);

    throw new sfActionStopException();
  }

  public function forwardIf ($condition, $module, $action)
  {
    if ($condition)
    {
      $this->forward($module, $action);
    }
  }

  public function forwardUnless ($condition, $module, $action)
  {
    if (!$condition)
    {
      $this->forward($module, $action);
    }
  }

  public function sendEmail($module, $action)
  {
    $presentation = $this->getPresentationFor($module, $action, 'sfMail');

    // error? (like a security forwarding)
    if (!$presentation)
    {
      throw new sfException('There was an error when trying to send this email.');
    }
  }

  public function getPresentationFor($module, $action, $viewName = null)
  {
    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfAction} get presentation for action "'.$module.'/'.$action.'" (view class: "'.$viewName.'")');

    $controller = $this->getController();

    // get original render mode
    $renderMode = $controller->getRenderMode();

    // set render mode to var
    $controller->setRenderMode(sfView::RENDER_VAR);

    // grab the action stack
    $actionStack = $controller->getActionStack();

    // grab this next forward's action stack index
    $index = $actionStack->getSize();

    // set viewName if needed
    if ($viewName)
    {
      $this->getRequest()->setAttribute($module.'_'.$action.'_view_name', $viewName, 'symfony/action/view');
    }

    // forward to the mail action
    $controller->forward($module, $action);

    // grab the action entry from this forward
    $actionEntry = $actionStack->getEntry($index);

    // get raw email content
    $presentation =& $actionEntry->getPresentation();

    // put render mode back
    $controller->setRenderMode($renderMode);

    // remove the action entry
    for ($i = $index; $i < $actionStack->getSize(); $i++)
    {
      $actionEntry = $actionStack->removeEntry($i);
    }

    // remove viewName
    if ($viewName)
    {
      $this->getRequest()->setAttribute($module.'_'.$action.'_view_name', '', 'symfony/action/view');
    }

    return $presentation;
  }

  /**
   * Redirects current request to a new URL.
   *
   * 2 URL formats are accepted :
   * - a full URL: http://www.google.com/
   * - an internal URL (url_for() format): 'ModuleName/ActionName'
   *
   * This method must be called as with a return:
   *
   * <code>return $this->redirect('/ModuleName/ActionName')</code>
   *
   * @param  string url
   * @return sfView::NONE
   */
  public function redirect($url)
  {
    $url = $this->getController()->genUrl(null, $url);

    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfAction} redirect to "'.$url.'"');

    $this->getController()->redirect($url);

    throw new sfActionStopException();
  }

  public function redirectIf ($condition, $url)
  {
    if ($condition)
    {
      $this->redirect($url);
    }
  }

  public function redirectUnless ($condition, $url)
  {
    if (!$condition)
    {
      $this->redirect($url);
    }
  }

  /**
   * Renders text to the browser, bypassing templating system.
   *
   * @param  string text to render to the browser
   * @return sfView::NONE
   */
  public function renderText($text)
  {
    echo $text;

    return sfView::NONE;
  }

  /**
   * Retrieve the default view to be executed when a given request is not
   * served by this action.
   *
   * @return mixed A string containing the view name associated with this
   *               action.
   *
   *               Or an array with the following indices:
   *
   *               - The parent module of the view that will be executed.
   *               - The view that will be executed.
   */
  public function getDefaultView ()
  {
    return sfView::INPUT;
  }

  /**
   * Retrieve the request methods on which this action will process
   * validation and execution.
   *
   * @return int One of the following values:
   *
   *             - sfRequest::GET
   *             - sfRequest::POST
   *             - sfRequest::NONE
   *
   * @see sfRequest
   */
  public function getRequestMethods ()
  {
    return sfRequest::GET | sfRequest::POST | sfRequest::NONE;
  }

  /**
   * Execute any post-validation error application logic.
   *
   * @return mixed A string containing the view name associated with this
   *               action.
   *
   *               Or an array with the following indices:
   *
   *               - The parent module of the view that will be executed.
   *               - The view that will be executed.
   */
  public function handleError ()
  {
    return sfView::ERROR;
  }

  /**
   * Manually register validators for this action.
   *
   * @param ValidatorManager A ValidatorManager instance.
   *
   * @return void
   */
  public function registerValidators ($validatorManager)
  {
  }

  /**
   * Manually validate files and parameters.
   *
   * @return bool true, if validation completes successfully, otherwise false.
   */
  public function validate ()
  {
    return true;
  }

  /**
   * Indicates that this action requires security.
   *
   * @param  string action name (defaults to the current action)
   * @return bool true, if this action requires security, otherwise false.
   */
  public function isSecure()
  {
    if (isset($this->security[$this->getActionName()]['is_secure']))
    {
      return $this->security[$this->getActionName()]['is_secure'];
    }

    if (isset($this->security['all']['is_secure']))
    {
      return $this->security['all']['is_secure'];
    }

    return false;
  }

  /**
   * Gets credentials the user must have to access this action.
   *
   * @param  string action name (defaults to the current action)
   * @return mixed
   */
  public function getCredential()
  {
    if (isset($this->security[$this->getActionName()]['credentials']))
    {
      $credentials = $this->security[$this->getActionName()]['credentials'];
    }
    else if (isset($this->security['all']['credentials']))
    {
      $credentials = $this->security['all']['credentials'];
    }
    else
    {
      $credentials = null;
    }

    return $credentials;
  }

  public function setTemplate($name)
  {
    if (sfConfig::get('sf_logging_active')) $this->getContext()->getLogger()->info('{sfAction} change template to "'.$name.'"');

    $this->template = $name;
  }

  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * DEPRECATED: Please use the sfResponse object
   */
  public function addHttpMeta($key, $value, $override = true)
  {
    $this->getContext()->getLogger()->err('This method is deprecated. Please use $this->getResponse()->addHttpMeta($key, $value, $override).');
    $this->getContext()->getResponse()->addHttpMeta($key, $value, $override);
  }

  /**
   * DEPRECATED: Please use the sfResponse object
   */
  public function addMeta($key, $value, $override = true)
  {
    $this->getContext()->getLogger()->err('This method is deprecated. Please use $this->getResponse()->addMeta($key, $value, $override).');
    $this->getContext()->getResponse()->addMeta($key, $value, $override);
  }

  /**
   * DEPRECATED: Please use the sfResponse object
   */
  public function setTitle($title)
  {
    $this->getContext()->getLogger()->err('This method is deprecated. Please use $this->getResponse()->setTitle($title).');
    $this->getContext()->getResponse()->setTitle($title);
  }

  /**
   * DEPRECATED: Please use the sfResponse object
   */
  public function addStylesheet($css, $position = '', $options = array())
  {
    $this->getContext()->getLogger()->err('This method is deprecated. Please use $this->getResponse()->addStylesheet($css, $position, $options).');
    $this->getContext()->getResponse()->addStylesheet($css, $position, $options);
  }

  /**
   * DEPRECATED: Please use the sfResponse object
   */
  public function addJavascript($js)
  {
    $this->getContext()->getLogger()->err('This method is deprecated. Please use $this->getResponse()->addJavascript($js).');
    $this->getContext()->getResponse()->addJavascript($js);
  }
}

?>