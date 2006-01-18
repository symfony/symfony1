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
 * @version    SVN: $Id: sfAction.class.php 527 2005-10-17 14:02:12Z fabien $
 */
abstract class sfAction
{
  const ALL = 'ALL';

  private
    $context                  = null,
    $var_holder               = null,
    $security                 = array(),
    $request                  = null,
    $request_parameter_holder = null,
    $template                 = '';

  /**
   * Execute any application/business logic for this action.
   *
   * In a typical database-driven application, execute() handles application
   * logic itself and then proceeds to create a model instance. Once the model
   * instance is initialized it handles all business logic for the action.
   *
   * A model should represent an entity in your application. This could be a
   * user account, a shopping cart, or even a something as simple as a
   * single product.
   *
   * @return mixed A string containing the view name associated with this action.
   *
   *               Or an array with the following indices:
   *
   *               - The parent module of the view that will be executed.
   *               - The view that will be executed.
   */
  abstract function execute ();

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
    $this->context                  = $context;
    $this->var_holder               = new sfParameterHolder();
    $this->request                  = $context->getRequest();
    $this->request_parameter_holder = $this->request->getParameterHolder();

    // include security configuration
    require(sfConfigCache::checkConfig('modules/'.$this->getModuleName().'/'.sfConfig::get('sf_app_module_config_dir_name').'/security.yml', true, array('moduleName' => $this->getModuleName())));

    return true;
  }

  public function preExecute ()
  {
  }

  public function postExecute ()
  {
  }

  /**
   * Retrieve the current application context.
   *
   * @return sfContext The current sfContext instance.
   */
  public final function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve the current logger instance.
   *
   * @return sfLogger The current sfLogger instance.
   */
  public final function getLogger ()
  {
    return $this->context->getLogger();
  }

  /**
   * Log $message using sfLogger object.
   * 
   * @param mixed  String or object containing the message to log.
   * @param string The priority of the message
   *               (available priorities: emerg, alert, crit, err, warning, notice, info, debug).
   */
  public function logMessage ($message, $priority = 'info')
  {
    return $this->context->getLogger()->log($message, constant('PEAR_LOG_'.strtoupper($priority)));
  }

  public function debugMessage ($message)
  {
    if (sfConfig::get('sf_web_debug'))
    {
      sfWebDebug::getInstance()->logShortMessage($message);
    }
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

  public function redirect_if ($condition, $url)
  {
    if ($condition)
    {
      $this->redirecti($url);
    }
  }

  public function redirect_unless ($condition, $url)
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
   * Returns the value of a request parameter.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->get($name)</code>
   *
   * @param  $name parameter name
   * @return string
   */
  public function getRequestParameter($name, $default = null)
  {
    return $this->request_parameter_holder->get($name, $default);
  }

  /**
   * Returns true if a request parameter exists.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->has($name)</code>
   *
   * @param  $name parameter name
   * @return boolean
   */
  public function hasRequestParameter($name)
  {
    return $this->request_parameter_holder->has($name);
  }

  /**
   * Returns the current request object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRequest()</code>
   *
   * @return object current request object
   */
  public function getRequest()
  {
    return $this->request;
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
    // disable security on [sf_login_module] / [sf_login_action]
    if ((sfConfig::get('sf_login_module') == $this->getModuleName()) && (sfConfig::get('sf_login_action') == $this->getActionName()))
    {
      return false;
    }

    // read security.yml configuration
    if (isset($this->security[$this->getActionName()]['is_secure']))
    {
      return $this->security[$this->getActionName()]['is_secure'];
    }
    else if (isset($this->security['all']) && isset($this->security['all']['is_secure']))
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
    else if (isset($this->security['all']) && isset($this->security['all']['credentials']))
    {
      $credentials = $this->security['all']['credentials'];
    }
    else
    {
      $credentials = null;
    }

    return $credentials;
  }

  public function getController()
  {
    return $this->getContext()->getController();
  }

  public function getUser()
  {
    return $this->getContext()->getUser();
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

  public function setVar($name, $value)
  {
    $this->var_holder->set($name, $value);
  }

  public function getVar($name)
  {
    return $this->var_holder->get($name);
  }

  public function getVarHolder()
  {
    return $this->var_holder;
  }

  /**
   * Sets a variable for the template.
   *
   * This is just really a shortcut for:
   * <code>$this->setVar('name', 'value')</code>
   *
   * @param  string key
   * @param  string value
   * @return boolean always true
   */
  public function __set($key, $value)
  {
    return $this->var_holder->setByRef($key, $value);
  }

  /**
   * Gets a variable for the template.
   *
   * This is just really a shortcut for:
   * <code>$this->getVar('name')</code>
   *
   * @param  string key
   * @return mixed
   */
  public function __get($key)
  {
    return $this->var_holder->get($key);
  }

  public function addHttpMeta($key, $value, $override = true)
  {
    if ($override || !$this->request->hasAttribute($key, 'helper/asset/auto/httpmeta'))
    {
      $this->request->setAttribute($key, $value, 'helper/asset/auto/httpmeta');
    }
  }

  public function addMeta($key, $value, $override = true)
  {
    if ($override || !$this->request->hasAttribute($key, 'helper/asset/auto/meta'))
    {
      $this->request->setAttribute($key, $value, 'helper/asset/auto/meta');
    }
  }

  public function setTitle($title)
  {
    $this->request->getAttributeHolder()->set('title', $title, 'helper/asset/auto/meta');
  }

  public function addStylesheet($css, $position = '')
  {
    if ($position == 'first')
    {
      $this->request->setAttribute($css, $css, 'helper/asset/auto/stylesheet/first');
    }
    else if ($position == 'last')
    {
      $this->request->setAttribute($css, $css, 'helper/asset/auto/stylesheet/last');
    }
    else
    {
      $this->request->setAttribute($css, $css, 'helper/asset/auto/stylesheet');
    }
  }

  public function addJavascript($js)
  {
    $this->request->setAttribute($js, $js, 'helper/asset/auto/javascript');
  }

  public function setFlash($name, $value, $persist = true)
  {
    $this->getUser()->setAttribute($name, $value, 'symfony/flash');

    if (!$persist)
    {
      $this->getUser()->setAttribute($name, true, 'symfony/flash/remove');
    }
  }

  public function getFlash($name)
  {
    return $this->getUser()->getAttribute($name, null, 'symfony/flash');
  }

  public function hasFlash($name)
  {
    return $this->getUser()->hasAttribute($name, 'symfony/flash');
  }
}

?>