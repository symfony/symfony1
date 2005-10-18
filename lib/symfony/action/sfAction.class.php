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
 * sfAction executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfAction.class.php 527 2005-10-17 14:02:12Z fabien $
 */
abstract class sfAction
{
  const ALL = 'ALL';

  private
    $context    = null,
    $var_holder = null,
    $security   = array(),
    $template   = '';

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
    $this->context = $context;
    $this->var_holder = new sfParameterHolder();

    // include security configuration
    require(sfConfigCache::checkConfig('modules/'.$this->getModuleName().'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/security.yml', true, array('moduleName' => $this->getModuleName())));

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
    if (!SF_CACHE)
    {
      return 1;
    }

    // ignore cache? (only in debug mode)
    if (SF_DEBUG && $this->getRequest()->getParameter('ignore_cache', false, 'symfony/request/sfWebRequest') == true)
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

  public function forward404_unless ($condition)
  {
    if (!$condition)
    {
      throw new sfError404Exception();
    }
  }

  public function forward404_if ($condition)
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
    return $this->redirect('/'.SF_ERROR_404_MODULE.'/'.SF_ERROR_404_ACTION);
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
    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfAction} forward to action "'.$module.'/'.$action.'"');

    $this->getController()->forward($module, $action);

    throw new sfActionStopException();
  }

  public function forward_if ($condition, $module, $action)
  {
    if ($condition)
    {
      $this->forward($module, $action);
    }
  }

  public function forward_unless ($condition, $module, $action)
  {
    if (!$condition)
    {
      $this->forward($module, $action);
    }
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

    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfAction} redirect to "'.$url.'"');

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
    return $this->getRequest()->getParameterHolder()->get($name, $default);
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
    return $this->getRequest()->getParameterHolder()->has($name);
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
    return $this->getContext()->getRequest();
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
    else if (isset($this->security['all']['is_secure']))
    {
      return $this->security['all']['is_secure'];
    }
    else
    {
      return false;
    }
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
      return $this->security[$this->getActionName()]['credentials'];
    }
    else if (isset($this->security['all']['credentials']))
    {
      return $this->security['all']['credentials'];
    }
    else
    {
      return null;
    }
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
    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfAction} change template to "'.$name.'"');

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
    if ($override || !$this->getRequest()->hasAttribute($key, 'helper/asset/auto/httpmeta'))
    {
      $this->getRequest()->setAttribute($key, $value, 'helper/asset/auto/httpmeta');
    }
  }

  public function addMeta($key, $value, $override = true)
  {
    if ($override || !$this->getRequest()->hasAttribute($key, 'helper/asset/auto/meta'))
    {
      $this->getRequest()->setAttribute($key, $value, 'helper/asset/auto/meta');
    }
  }

  public function setTitle($title)
  {
    $this->getRequest()->getAttributeHolder()->set('title', $title, 'helper/asset/auto/meta');
  }

  public function addStylesheet($css)
  {
    $this->getRequest()->setAttribute($css, $css, 'helper/asset/auto/stylesheet');
  }

  public function addJavascript($js)
  {
    $this->getRequest()->setAttribute($js, $js, 'helper/asset/auto/javascript');
  }
}

?>